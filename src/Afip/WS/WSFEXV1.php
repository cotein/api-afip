<?php

namespace Cotein\ApiAfip\Afip\WS;

use Cotein\ApiAfip\Constantes;
use Illuminate\Support\Facades\Log;


class WSFEV1 extends WebService
{
    const SERVICE = 'WSFEXV1';

    /**
     * Method __construct
     *
     * @param $environment $environment, entorno en AFIP testing ó production
     * @param $cuit=20227339730 $cuit de la empresa que va a realizar la 
     * facturación electrónica que ha delegado el servicio a nombre de DMIT.
     * Por defecto mi CUIT
     *
     * @return void
     */
    public function __construct($environment = 'testing', $company_cuit = Constantes::DIEGO_BARRUETA_CUIT, $company_id, $user_id)
    {
        parent::__construct(self::SERVICE, $environment, $company_cuit, $company_id, $user_id);

        $this->afip_params = [
            'Auth' => [
                'Token' => $this->token,
                'Sign' => $this->sign,
                'Cuit' => $this->cuitRepresentada
            ]
        ];

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
}
