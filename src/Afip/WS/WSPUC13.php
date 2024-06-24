<?php

namespace Cotein\ApiAfip\Afip\WS;

use Cotein\ApiAfip\Constantes;
use Illuminate\Support\Facades\Log;
use SoapClient;
use Exception;

class WSPUC13 extends WebService
{
    const SERVICE = 'ws_sr_padron_a13';

    public function __construct($environment = 'testing', $company_cuit, $company_id, $user_id)
    {
        parent::__construct(self::SERVICE, $environment, $company_cuit, $company_id, $user_id);

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
            Log::error("Error en try catch WSPUC13" . $e->getMessage() . ' - ' . $e->getCode());
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

    public function getPersonaByDocumento($dni)
    {
        $this->afip_params['documento'] = $dni;

        try {
            $result = $this->soapHttp->getIdPersonaListByDocumento($this->afip_params);
            if (is_soap_fault($result)) {
                return response()->json($result, 500);
            }
            return json_decode(json_encode($result), true);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }
}
