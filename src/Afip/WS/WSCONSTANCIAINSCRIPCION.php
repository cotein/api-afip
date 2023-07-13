<?php

namespace Cotein\ApiAfip\Afip\WS;

use Illuminate\Support\Facades\Log;


class WSCONSTANCIAINSCRIPCION extends WebService
{
    const SERVICE = 'ws_sr_constancia_inscripcion';

    public function __construct($environment = 'testing')
    {
        parent::__construct(self::SERVICE, $environment);

        $this->afip_params['Auth'] = $this->Auth;

        $this->connect();
    }

    public function connect(): void
    {
        try {

            $wsdl = "{$this->service}_{$this->environment}";

            $ws = WS_CONST::getWSDL($wsdl);

            $this->soapHttp = new \SoapClient($ws);
        } catch (\Exception $e) {

            Log::error("Error en try catch WSPUC" . $e->getMessage() . ' - ' . $e->getCode());

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
}
