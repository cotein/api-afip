<?php

namespace Cotein\ApiAfip\Tests;

use Cotein\ApiAfip\Afip\WS\WSFEV1;
use Cotein\ApiAfip\Afip\WS\WSPUC13;
use Cotein\ApiAfip\AfipWebService;


class FindWebServicesTest extends \Orchestra\Testbench\TestCase
{
    /** @test */
    function can_get_factura_web_service()
    {
        $ws = AfipWebService::findWebService('factura', 'production');

        $this->assertInstanceOf(WSFEV1::class, $ws);
    }

    /** @test */
    function can_get_padron_web_service()
    {
        $ws = AfipWebService::findWebService('padron', 'production');

        $this->assertInstanceOf(WSPUC13::class, $ws);
    }
}
