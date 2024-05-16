<?php

namespace Cotein\ApiAfip\Afip\WS;

use Illuminate\Support\Facades\Log;

class WSPUC13 extends WebService
{
    const SERVICE = 'ws_sr_padron_a13';

    public function __construct($environment = 'testing', $company_cuit, $company_id, $user_id)
    {
        parent::__construct(self::SERVICE, $environment, 20227339730, 1, 1);

        $this->afip_params = [];
        $this->afip_params['token'] = $this->Auth['Token'];
        $this->afip_params['sign'] = $this->Auth['Sign'];
        $this->afip_params['cuitRepresentada'] = $this->cuitRepresentada;

        $this->connect();
    }

    public function connect(): void
    {
        try {

            $wsdl = strtoupper(self::SERVICE) . '_' . $this->environment;

            $ws = WS_CONST::getWSDL($wsdl);

            $this->soapHttp = new \SoapClient($ws);

            /* $header = new \SoapHeader('Access-Control-Allow-Origin', '*');

            $www = new \SoapHeader('Content-Type', 'text/xml');

            $this->soapHttp->__setSoapHeaders($header);
            $this->soapHttp->__setSoapHeaders($www); */
        } catch (\Exception $e) {

            Log::error("Error en try catch WSPUC13" . $e->getMessage() . ' - ' . $e->getCode());

            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Method dummy
     * Método Dummy para verificación de funcionamiento de infraestructura (FEDummy)
     * @return void
     */
    public function dummy()
    {
        return $this->soapHttp->dummy();
    }
    /**
     * Method functions
     * Retorna las funciones del WS
     * @return void
     */
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

            $r =  json_decode(json_encode($result), true);

            return $r;
        } catch (\Exception $e) {

            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }
}
