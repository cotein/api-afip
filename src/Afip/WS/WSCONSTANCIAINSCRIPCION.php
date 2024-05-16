<?php

namespace Cotein\ApiAfip\Afip\WS;

use Illuminate\Support\Facades\Log;


class WSCONSTANCIAINSCRIPCION extends WebService
{
    const SERVICE = 'ws_sr_constancia_inscripcion';

    public function __construct($environment = 'testing', $company_cuit, $company_id, $user_id)
    {
        //parent::__construct(self::SERVICE, $environment, 20227339730, 1, 1);
        parent::__construct(self::SERVICE, $environment, $company_cuit, $company_id, $user_id);

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
        } catch (\Exception $e) {

            Log::error("Error en try catch WSPUC" . $e->getMessage() . ' - ' . $e->getCode());

            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Method getPersona
     *
     * @param $cuit $cuit Cuit del sujeto a buscar, sólo números
     *
     * @return getPersonaResponse
     */
    public function getPersona($cuit)
    {
        $this->afip_params['idPersona'] = $cuit;

        try {
            $result = $this->soapHttp->getPersona($this->afip_params);

            if (is_soap_fault($result)) {
                return response()->json($result, 500);
            }

            $r =  json_decode(json_encode($result), true);

            return $r;
        } catch (\Exception $e) {

            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Method getPersona_v2
     *
     * @param $cuit $cuit Cuit del sujeto a buscar, sólo números
     *
     * @return getPersonaResponse
     */
    public function getPersona_v2($cuit)
    {
        $this->afip_params['idPersona'] = $cuit;

        try {
            $result = $this->soapHttp->getPersona_v2($this->afip_params);

            if (is_soap_fault($result)) {
                return response()->json($result, 500);
            }

            $r =  json_decode(json_encode($result), true);

            return $r;
        } catch (\Exception $e) {

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
}
