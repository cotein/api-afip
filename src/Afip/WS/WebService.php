<?php

namespace Cotein\ApiAfip\Afip\WS;

use Cotein\ApiAfip\Models\AfipToken;
use Jenssegers\Date\Date;
use Illuminate\Support\Facades\Log;
use Exception;
use SoapClient;

// URL DOC https://www.afip.gob.ar/fe/ayuda/documentos/wsfev1-COMPG.pdf

abstract class WebService
{
    protected $soapHttp;

    public $TA;

    public $TRA;

    public $TRA_TMP;

    public $environment;

    public $service;

    public $Auth;

    public $afip_params;

    public $token;

    public $sign;

    public $cuit;

    public $cuitRepresentada;

    public $afipModel;

    public $user;

    public function __construct($service, $environment, $user = null)
    {
        $this->service = strtoupper($service);
        $this->environment = strtoupper($environment);
        $this->user = $user;

        $this->cuit = env('WS_AFIP_CUIT');

        (is_null($user))
            ? $this->cuitRepresentada = env('WS_AFIP_CUIT_REPRESENTADA')
            : $this->cuitRepresentada = $user->company_cuit;

        ini_set("soap.wsdl_cache_enabled", 0);
        ini_set('soap.wsdl_cache_ttl', 0);

        /* $this->token = $this->afipModel->token;

        $this->sign = $this->afipModel->sign; */
        $this->create_TA($service);

        $this->Auth = [
            'Token' => $this->get_Token(),
            'Sign'  => $this->get_sign(),
            'Cuit'  => $this->cuitRepresentada
        ];
    }

    /**
     * Method saveAfipModel
     * Registra en la base de datos los datos del webServices que se conecta
     * @return void
     */
    function saveAfipModel($user = null): void
    {

        $this->afipModel = new AfipToken();
        $this->afipModel->name = $this->service;
        $this->afipModel->unique_id = $this->get_unique_id();
        $this->afipModel->generation_time = $this->get_generationTime();
        $this->afipModel->expiration_time = $this->get_expirationTime();
        $this->afipModel->token = $this->get_Token();
        $this->afipModel->sign = $this->get_sign();
        $this->afipModel->environment = $this->environment;
        $this->afipModel->active = true;
        /* $this->afipModel->user_id = $user->id;
        $this->afipModel->company_id = $user->company->id; */
        $this->afipModel->save();
    }

    function saveToken($service)
    {
        if (
            !AfipToken::where('ws', $this->service)
                ->where('active', true)
                ->where('environment', $this->environment)
                ->exists()
        ) {
            //persisto los datos
            $this->create_TA($service);
        } else {

            $this->afipModel = AfipToken::where('ws', $this->service)->where('environment', $this->environment)->where('active', true)->get()->first();
        }
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
     * Method create_TRA
     *
     * @param $service Crea el archivo xml de TRA
     * $service - se reemplaza por el ws que se desea usar
     *
     * @return void
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
        //unlink(WS_CONST::TRA_TMP);
        return $CMS;
    }


    public function call_WSAA($cms)
    {
        $client = new \SoapClient(
            ($this->environment == 'PRODUCTION'
                ? WS_CONST::WSAA_PRODUCTION_WSDL
                : WS_CONST::WSAA_TESTING_WSDL
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
            ]
        );

        $results = $client->loginCms(['in0' => $cms]);
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

    abstract public function connect(): void;
}
