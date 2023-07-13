<?php

namespace Cotein\ApiAfip\Facades;

use Illuminate\Support\Facades\Facade;


class AfipWebService extends Facade
{

    protected static function getFacadeAccessor()
    {
        return 'AfipWebService';
    }
}
