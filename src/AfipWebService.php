<?php

namespace Cotein\ApiAfip;

use Cotein\ApiAfip\Afip\WS\WebService;
use Cotein\ApiAfip\Afip\WS\WS_CONST;

class AfipWebService
{
    public static function findWebService(string $service, string $environment, $user = null): WebService
    {
        $serv = strtoupper($service);

        $ws = WS_CONST::find($serv);

        return new $ws($environment, $user);
    }
}
