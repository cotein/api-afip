<?php

namespace Cotein\ApiAfip\Afip\WS;

use Jenssegers\Date\Date;


class WSAA
{
    const WSBASE_CUIT = 23263268709;

    const WSBASE_CUIT_REPRESENTADA = 20233374971;

    public $cuit;

    public $cuitRepresentada;

    public $environment;

    public $service;

    public function __construct()
    {
    }

    function connect($service, $environment)
    {
        $this->service = strtoupper($service);
        $this->environment = strtoupper($environment);

        $this->cuit = self::WSBASE_CUIT;
        $this->cuitRepresentada = self::WSBASE_CUIT_REPRESENTADA;
        //dd('cuit ' . $this->cuit, 'environment ' . $environment);

        ini_set("soap.wsdl_cache_enabled", 0);
        ini_set('soap.wsdl_cache_ttl', 0);

        $this->create_TA($service, $this->environment);
    }

    /**
     * Abre el archivo de TA xml,
     * si hay algun problema devuelve false
     */
    public function openTA()
    {
        $TA = simplexml_load_file(WS_CONST::TA);

        return $TA == false ? false : true;
    }

    /**
     * Crea el archivo xml de TRA
     * $service - se reemplaza por el ws que se desea usar
     */
    public  function create_TRA($service)
    {
        $TRA = new \SimpleXMLElement(
            '<?xml version="1.0" encoding="UTF-8"?>' .
                '<loginTicketRequest version="1.0">' .
                '</loginTicketRequest>'
        );
        $TRA->addChild('header');
        $TRA->header->addChild('uniqueId', date('U'));
        $TRA->header->addChild('generationTime', date('c', date('U') - 60));
        $TRA->header->addChild('expirationTime', date('c', date('U') + 60));
        $TRA->addChild('service', $service);
        $TRA->asXML(WS_CONST::TRA);
    }

    public function get_service()
    {
        $TRA = simplexml_load_file(WS_CONST::TRA);

        return $TRA->service;
    }

    public  function sign_TRA()
    {
        $STATUS = openssl_pkcs7_sign(
            WS_CONST::TRA,
            WS_CONST::TRA_TMP,
            file_get_contents(
                ($this->environment == 'PRODUCTION'
                    ? WS_CONST::PRODUCTION_CERTIFICATE
                    : WS_CONST::TESTING_CERTIFICATE
                )
            ),
            [
                file_get_contents(
                    ($this->environment == 'PRODUCTION'
                        ? WS_CONST::PRODUCTION_PRIVATE_KEY
                        : WS_CONST::TESTING_PRIVATE_KEY
                    )
                ), ''
            ],
            [],
            !PKCS7_DETACHED
        );

        if (!$STATUS)
            throw new \Exception("ERROR generating PKCS#7 signature");

        $inf = fopen(WS_CONST::TRA_TMP, "r");
        $i = 0;
        $CMS = "";
        while (!feof($inf)) {
            $buffer = fgets($inf);
            if ($i++ >= 4) $CMS .= $buffer;
        }
        fclose($inf);
        //---## BORRO EL TEMPORAL ##---//
        unlink(WS_CONST::TRA_TMP);
        return $CMS;
    }


    public function call_WSAA($cms)
    {
        $ops = [
            'ssl' =>
            [
                //'ciphers' => 'AES256-SHA',
                'verify_peer' => false,
                'verify_peer_name' => false
            ]
        ];

        $client = new \SoapClient(
            ($this->environment == 'PRODUCTION'
                ? "file://" . WS_CONST::WSAA_WSDL_PRODUCTION
                : "file://" . WS_CONST::WSAA_WSDL_TESTING
            ),

            [
                'cache_wsdl'  => WSDL_CACHE_NONE,
                'soap_version'  => SOAP_1_2,
                'location'      => (
                    ($this->environment == 'PRODUCTION')
                    ? WS_CONST::WSAA_PRODUCTION_LOGINCMS
                    : WS_CONST::WSAA_TESTING_LOGINCMS
                ),
                'trace'         => 1,
                'exceptions'    => 0,
                //'stream_opts' => stream_context_create($ops)
            ]
        );
        /* $client->__setLocation(($this->environment == 'PRODUCTION'
            ? "file://" . WS_CONST::WSAA_PRODUCTION_LOGINCMS
            : "file://" . WS_CONST::WSAA_TESTING_LOGINCMS
        )); */

        $results = $client->loginCms(['in0' => $cms]);
        //dd($results);
        file_put_contents(
            WS_CONST::REQUEST_LOGIN_CMS,
            $client->__getLastRequest()
        );
        file_put_contents(
            WS_CONST::RESPONSE_LOGIN_CMS,
            $client->__getLastResponse()
        );
        if (is_soap_fault($results))
            throw new \Exception("SOAP Fault: " . $results->faultcode . ' : ' . $results->faultstring);

        return $results->loginCmsReturn;
    }

    /*
    * Convertir un XML en Array
    */
    public  function xml2array($xml)
    {

        $json = json_encode(simplexml_load_string($xml));

        return json_decode($json, TRUE);
    }

    public function create_TA($web_services)
    {
        $this->create_TRA($web_services);

        $CMS = $this->sign_TRA();

        $TICKET = $this->call_WSAA($CMS);

        if (!file_put_contents(WS_CONST::TA, $TICKET))
            throw new \Exception("Error al generar al archivo TA.xml");

        return true;
    }

    public function get_unique_id()
    {
        $TA_file = file(WS_CONST::TA, FILE_IGNORE_NEW_LINES);
        $TA_xml = '';
        for ($i = 0; $i < sizeof($TA_file); $i++)
            $TA_xml .= $TA_file[$i];

        $TA = $this->xml2Array($TA_xml);
        $uniqueId = $TA['header']['uniqueId'];

        return $uniqueId;
    }

    public function get_expirationTime()
    {
        $TA_file = file(WS_CONST::TA, FILE_IGNORE_NEW_LINES);
        $TA_xml = '';
        for ($i = 0; $i < sizeof($TA_file); $i++)
            $TA_xml .= $TA_file[$i];

        $TA = $this->xml2Array($TA_xml);
        $expirationTime = $TA['header']['expirationTime'];

        return $expirationTime;
    }

    public function get_generationTime()
    {
        $TA_file = file(WS_CONST::TA, FILE_IGNORE_NEW_LINES);
        $TA_xml = '';
        for ($i = 0; $i < sizeof($TA_file); $i++)
            $TA_xml .= $TA_file[$i];

        $TA = $this->xml2Array($TA_xml);
        $generationTime = $TA['header']['generationTime'];

        return $generationTime;
    }

    public function get_Token()
    {
        $TA_file = file(WS_CONST::TA, FILE_IGNORE_NEW_LINES);
        $TA_xml = '';
        for ($i = 0; $i < sizeof($TA_file); $i++)
            $TA_xml .= $TA_file[$i];

        $TA = $this->xml2Array($TA_xml);
        $token = $TA['credentials']['token'];

        return $token;
    }

    public function get_sign()
    {
        $TA_file = file(WS_CONST::TA, FILE_IGNORE_NEW_LINES);
        $TA_xml = '';
        for ($i = 0; $i < sizeof($TA_file); $i++)
            $TA_xml .= $TA_file[$i];

        $TA = $this->xml2Array($TA_xml);
        $sign = $TA['credentials']['sign'];

        return $sign;
    }

    public function is_validTA()
    {
        $c =  new Date;
        $expirationTime = $c->parse($this->get_expirationTime());
        $currentTime    = $c->parse($c->now());

        if (strtotime($currentTime) >= strtotime($expirationTime)) {
            return false;
        }

        return true;
    }
}
