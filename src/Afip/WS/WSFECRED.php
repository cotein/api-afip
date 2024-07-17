<?php

namespace Cotein\ApiAfip\Afip\WS;

use Cotein\ApiAfip\Constantes;
use Illuminate\Support\Facades\Log;
use SoapClient;
use Exception;

class WSFECRED extends WebService
{
    const SERVICE = 'wsfecred';

    protected $authRequest;
    public function __construct($environment = 'testing', $company_cuit, $company_id, $user_id)
    {
        parent::__construct(self::SERVICE, $environment, $company_cuit, $company_id, $user_id);

        $this->afip_params = [
            'authRequest' => [
                'token' => $this->token,
                'sign' => $this->sign,
                'cuitRepresentada' => $this->cuitRepresentada
            ]

        ];

        $this->connect();
    }

    public function connect(): void
    {
        try {
            $wsdl = strtoupper(self::SERVICE) . '_' . $this->environment;
            $ws = WS_CONST::getWSDL($wsdl);
            $this->soapHttp = new SoapClient($ws);
        } catch (Exception $e) {
            Log::error("Error en try catch WSPUC" . $e->getMessage() . ' - ' . $e->getCode());
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    public function consultarMontoObligadoRecepcion($cuitConsultada, $fechaEmision)
    {

        $this->afip_params['cuitConsultada'] = $cuitConsultada;
        $this->afip_params['fechaEmision'] = $fechaEmision;

        return $this->soapHttp->consultarMontoObligadoRecepcion($this->afip_params);
    }

    public function Dummy()
    {
        return $this->soapHttp->Dummy();
    }
}
