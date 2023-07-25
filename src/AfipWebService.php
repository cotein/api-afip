<?php

namespace Cotein\ApiAfip;

use Cotein\ApiAfip\Afip\WS\WebService;
use Cotein\ApiAfip\Afip\WS\WS_CONST;

class AfipWebService
{
    /**
     * Method findWebService
     *  Retorna el Web Service de Afip que necesito
     * @param string $service [explicite description]
     * @param string $environment [explicite description]
     * @param $user $user [explicite description]
     *
     * @return WebService
     */
    public static function findWebService(string $service, string $environment, $user = null): WebService
    {
        $serv = strtoupper($service);

        $ws = WS_CONST::find($serv);

        return new $ws($environment, $user);
    }
}
