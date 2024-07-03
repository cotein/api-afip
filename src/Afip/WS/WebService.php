<?php

namespace Cotein\ApiAfip\Afip\WS;

use App\Src\Helpers\Afip;
use Carbon\Carbon;
use Cotein\ApiAfip\Models\AfipToken;

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

    /**
     * Method __construct
     *
     * @param $service $service el nombre del SOAP Web service de AFIP
     * @param $environment $environment entorno en AFIP testing ó production
     * @param $company_cuit $company_cuit CUIT de la compañia que ejecuta el Web service
     * @param $company_id $company_id ID de la compañia que ejecuta el Web service
     * @param $user_id $user_id de quien ejecuta el Web service
     *
     * @return void
     */

    public function __construct($service, $environment, $company_cuit, $company_id, $user_id)
    {
        $this->service = strtoupper((string) $service);
        $this->environment = strtoupper((string) $environment);
        $this->cuit = env('WS_AFIP_CUIT');
        $this->cuitRepresentada = is_null($company_cuit) ? env('WS_AFIP_CUIT_REPRESENTADA') : $company_cuit;

        ini_set("soap.wsdl_cache_enabled", 0);
        ini_set('soap.wsdl_cache_ttl', 0);

        $this->afipModel = AfipToken::where('ws', $this->service)
            ->where('active', true)
            ->where('environment', $this->environment)
            ->where('company_id', $company_id)
            ->first();

        // Verificar si el modelo de Afip existe y si el token está activo y no ha expirado
        if (!$this->afipModel || $this->afipModel->isActive() == false) {
            // Log para depuración
            error_log("Token expirado o no encontrado. Generando uno nuevo...");

            if ($this->afipModel) {
                // Desactivar el token expirado antes de crear uno nuevo
                $this->afipModel->active = false;
                $this->afipModel->save();
            }

            $this->create_TA($service);
            $this->afipModel = $this->saveAfipModel($company_id, $user_id);
        }

        // Actualizar las propiedades de la instancia con el token y la firma
        $this->token = $this->afipModel->token;
        $this->sign = $this->afipModel->sign;

        $this->Auth = [
            'Token' => $this->token,
            'Sign'  => $this->sign,
            'Cuit'  => $this->cuitRepresentada
        ];
    }

    /**
     * Method saveAfipModel
     * Registra en la base de datos los datos del webServices que se conecta
     * @return void
     */
    function saveAfipModel($companyId, $userId): AfipToken
    {
        $afipModel = new AfipToken();
        $afipModel->ws = $this->service;
        $afipModel->unique_id = $this->get_unique_id();
        $afipModel->generation_time = $this->get_generationTime();
        $afipModel->expiration_time = $this->get_expirationTime();
        $afipModel->token = $this->get_Token();
        $afipModel->sign = $this->get_sign();
        $afipModel->environment = $this->environment;
        $afipModel->active = true;
        $afipModel->user_id = $userId;
        $afipModel->company_id = $companyId;
        $afipModel->save();
        $afipModel->refresh();

        return $afipModel;
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
        $c =  new Carbon();
        $expirationTime = $c->parse($this->get_expirationTime());
        $currentTime    = $c->parse($c->now());

        return ($currentTime->gt($expirationTime) ? false : true);
    }

    abstract public function connect(): void;
}
