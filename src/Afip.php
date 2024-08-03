<?php

namespace Cotein\ApiAfip;

use Cotein\ApiAfip\Afip\WS\ARBA;
use Cotein\ApiAfip\Afip\WS\WebService;
use Cotein\ApiAfip\Afip\WS\WS_CONST;

class Afip
{
    /**
     * Method findWebService
     *  Retorna el Web Service de Afip que necesito
     * @param string $service nombre del ws de afip
     * @param string $environment entorno donde se ejecuta el ws
     * @param integer $company_cuit 
     * @param integer $company_id 
     * @param integer $user_id 
     *
     * @return WebService
     */
    public static function findWebService(string $service, string $environment, $company_cuit, $company_id, $user_id)
    {
        $serv = strtoupper($service);

        $ws = WS_CONST::find($serv);

        return new $ws($environment, $company_cuit, $company_id, $user_id);
    }
}
