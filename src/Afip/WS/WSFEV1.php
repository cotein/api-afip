<?php

namespace Cotein\ApiAfip\Afip\WS;

use Illuminate\Support\Facades\Log;


class WSFEV1 extends WebService
{
    const SERVICE = 'wsfe';

    public function __construct($environment = 'testing', $user = null)
    {
        parent::__construct(self::SERVICE, $environment, $user);

        $this->afip_params['Auth'] = $this->Auth;

        $this->connect();
    }

    public function connect(): void
    {
        try {

            $wsdl = strtoupper(self::SERVICE) . '_' . $this->environment;

            $ws = WS_CONST::getWSDL($wsdl);

            $this->soapHttp = new \SoapClient(
                $ws,
                [
                    "cache_wsdl" => 0,
                    "connection_timeout" => 5,
                    "exceptions" => true,
                    "features" => 5,
                    "soap_version" => 2,
                    "trace" => true,
                    'stream_context' => stream_context_create(['ssl' => ['ciphers' => 'AES256-SHA', 'verify_peer' => false, 'verify_peer_name' => false]])
                ]
            );

            /* $header = new \SoapHeader('Access-Control-Allow-Origin', '*');

            $this->soapHttp->__setSoapHeaders($header); */
        } catch (\Exception $e) {

            Log::error("Error en try catch WSFEV1" . $e->getMessage() . ' - ' . $e->getCode());

            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }

    public function FEDummy()
    {
        return $this->soapHttp->FEDummy();
    }

    /**
     * Method FEParamGetTiposPaises
     * Método para consultar valores referenciales de códigos de países
     * (FEParamGetTiposPaises)
     * Esta operación permite consultar los códigos de países y descripción de los mismos.
     * @return void
     */
    public function FEParamGetTiposPaises()
    {
        return $this->soapHttp->FEParamGetTiposPaises($this->afip_params);
    }

    public function FEParamGetTiposTributos()
    {
        return $this->soapHttp->FEParamGetTiposTributos($this->afip_params);
    }

    public function FEParamGetPtosVenta()
    {
        return $this->soapHttp->FEParamGetPtosVenta($this->afip_params);
    }

    public function ConsultarComprobanteEmitido($CbteTipo, $PtoVta, $CbteNro)
    {
        $FeCompConsReq = [
            'CbteTipo' => (int) $CbteTipo,
            'CbteNro' =>  $CbteNro,
            'PtoVta' => (int) $PtoVta,
        ];

        $this->afip_params['FeCompConsReq'] = $FeCompConsReq;

        return $this->soapHttp->FECompConsultar($this->afip_params);
    }
}
