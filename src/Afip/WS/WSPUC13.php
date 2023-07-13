<?php

namespace Cotein\ApiAfip\Afip\WS;

use Illuminate\Support\Facades\Log;


class WSPUC13 extends WebService
{
    const SERVICE = 'ws_sr_padron_a13';

    public function __construct($environment = 'testing')
    {
        parent::__construct(self::SERVICE, $environment);

        $this->afip_params['Auth'] = $this->Auth;

        $this->connect();
    }

    public function connect()
    {
        try {

            $wsdl = "{$this->service}_{$this->environment}";

            $ws = WS_CONST::getWSDL($wsdl);

            $this->soapHttp = new \SoapClient($ws);
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
}
