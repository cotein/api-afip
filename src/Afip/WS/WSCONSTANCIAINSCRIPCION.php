<?php

namespace Cotein\ApiAfip\Afip\WS;

use Cotein\ApiAfip\Constantes;
use Illuminate\Support\Facades\Log;
use SoapClient;
use Exception;

class WSCONSTANCIAINSCRIPCION extends WebService
{
    const SERVICE = 'ws_sr_constancia_inscripcion';

    public function __construct($environment = 'testing', $company_cuit, $company_id, $user_id)
    {
        parent::__construct(self::SERVICE, $environment, Constantes::DIEGO_BARRUETA_CUIT, 1, 1);

        $this->afip_params = [
            'token' => $this->token,
            'sign' => $this->sign,
            'cuitRepresentada' => $this->cuitRepresentada
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

    public function getPersona($cuit)
    {
        $this->afip_params['idPersona'] = $cuit;

        return $this->callSoapMethod('getPersona');
    }

    public function getPersona_v2($cuit)
    {
        $this->afip_params['idPersona'] = $cuit;

        return $this->callSoapMethod('getPersona_v2');
    }

    private function callSoapMethod($methodName)
    {
        try {
            $result = $this->soapHttp->$methodName($this->afip_params);

            if (is_soap_fault($result)) {
                return response()->json($result, 500);
            }

            return json_decode(json_encode($result), true);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    public function dummy()
    {
        return $this->soapHttp->dummy();
    }

    public function functions()
    {
        return $this->soapHttp->__getFunctions();
    }
}
