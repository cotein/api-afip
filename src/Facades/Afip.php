<?php

namespace Cotein\ApiAfip\Facades;

use Illuminate\Support\Facades\Facade;


class Afip extends Facade
{

    protected static function getFacadeAccessor()
    {
        return 'Afip';
    }
}
