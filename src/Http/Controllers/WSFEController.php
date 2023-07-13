<?php

namespace Cotein\ApiAfip\Http\Controllers;

use Cotein\ApiAfip\Facades\AfipWebService;

class WSFEController
{

    function index()
    {
        return AfipWebService::pp();
    }
}
