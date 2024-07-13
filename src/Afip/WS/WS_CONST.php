<?php

namespace Cotein\ApiAfip\Afip\WS;

use PhpParser\Node\Stmt\Return_;

class WS_CONST
{
    const WSFE_PRODUCTION = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Wsdl' . DIRECTORY_SEPARATOR . 'Production' . DIRECTORY_SEPARATOR . 'WSFE.wsdl';
    const WSFE_TESTING = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Wsdl' . DIRECTORY_SEPARATOR . 'Testing' . DIRECTORY_SEPARATOR . 'WSFE.wsdl';

    const WS_SR_PADRON_A13_PRODUCTION = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Wsdl' . DIRECTORY_SEPARATOR . 'Production' . DIRECTORY_SEPARATOR . 'WS_SR_PADRON_A13.wsdl';
    const WS_SR_PADRON_A13_TESTING = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Wsdl' . DIRECTORY_SEPARATOR . 'Testing' . DIRECTORY_SEPARATOR . 'WS_SR_PADRON_A13.wsdl';

    const WS_SR_CONSTANCIA_INSCRIPCION_PRODUCTION = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Wsdl' . DIRECTORY_SEPARATOR . 'Production' . DIRECTORY_SEPARATOR . 'WSCONSTANCIAINSCRIPCION.wsdl';
    const WS_SR_CONSTANCIA_INSCRIPCION_TESTING = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Wsdl' . DIRECTORY_SEPARATOR . 'Testing' . DIRECTORY_SEPARATOR . 'WSCONSTANCIAINSCRIPCION.wsdl';

    const WSFECRED_PRODUCTION = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Wsdl' . DIRECTORY_SEPARATOR . 'Production' . DIRECTORY_SEPARATOR . 'WSFECRED.wsdl';
    const WSFECRED_TESTING = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Wsdl' . DIRECTORY_SEPARATOR . 'Testing' . DIRECTORY_SEPARATOR . 'WSFECRED.wsdl';

    // PRODUCTION//
    const PRODUCTION_CERTIFICATE = DIRECTORY_SEPARATOR . 'usr' . DIRECTORY_SEPARATOR . 'afip-certificates' . DIRECTORY_SEPARATOR . 'DIMA_PRODUCTION.crt';
    const PRODUCTION_PRIVATE_KEY =  DIRECTORY_SEPARATOR . 'usr' . DIRECTORY_SEPARATOR . 'afip-certificates' . DIRECTORY_SEPARATOR . 'ClavePrivadaCoto.key';

    // TESTING//
    const TESTING_CERTIFICATE = DIRECTORY_SEPARATOR . 'usr' . DIRECTORY_SEPARATOR . 'afip-certificates' . DIRECTORY_SEPARATOR . 'DIMA_TESTING.crt';
    const TESTING_PRIVATE_KEY = DIRECTORY_SEPARATOR . 'usr' . DIRECTORY_SEPARATOR . 'afip-certificates' . DIRECTORY_SEPARATOR . 'ClavePrivadaCoto.key';

    const TA = __DIR__ . DIRECTORY_SEPARATOR . 'Xml' . DIRECTORY_SEPARATOR . 'TA.xml';

    const TRA = __DIR__ . DIRECTORY_SEPARATOR . 'Xml' . DIRECTORY_SEPARATOR . 'TRA.xml';

    const TRA_TMP = __DIR__ . DIRECTORY_SEPARATOR . 'Xml' . DIRECTORY_SEPARATOR . 'TRA.tmp';

    const WSAA_WSDL_PRODUCTION = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Wsdl' . DIRECTORY_SEPARATOR . 'Production' . DIRECTORY_SEPARATOR . 'WSAA.wsdl';

    const WSAA_WSDL_TESTING = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Wsdl' . DIRECTORY_SEPARATOR . 'Testing' . DIRECTORY_SEPARATOR . 'WSAA.wsdl';

    const REQUEST_LOGIN_CMS  = __DIR__ . DIRECTORY_SEPARATOR . 'Xml' . DIRECTORY_SEPARATOR . 'request-loginCms.xml';

    const RESPONSE_LOGIN_CMS = __DIR__ . DIRECTORY_SEPARATOR . 'Xml' . DIRECTORY_SEPARATOR . 'response-loginCms.xml';

    const WSAA_TESTING_LOGINCMS     = 'https://wsaahomo.afip.gov.ar/ws/services/LoginCms';
    const WSAA_TESTING_WSDL         = 'https://wsaahomo.afip.gov.ar/ws/services/LoginCms?wsdl';
    const WSAA_PRODUCTION_LOGINCMS  = 'https://wsaa.afip.gov.ar/ws/services/LoginCms';
    const WSAA_PRODUCTION_WSDL      = 'https://wsaa.afip.gov.ar/ws/services/LoginCms?wsdl';


    public static function getWSDL($wsdl)
    {
        $class = new \ReflectionClass(__CLASS__);

        $constants = $class->getConstants();

        return $constants[$wsdl];
    }

    public static function find($name)
    {
        $path = 'Cotein\ApiAfip\Afip\WS';

        $ws = [
            'FACTURA' => "{$path}\WSFEV1",
            'PADRON' => "{$path}\WSPUC13",
            'CONSTANCIA' => "{$path}\WSCONSTANCIAINSCRIPCION",
            'WSFECRED' => "{$path}\WSFECRED",
        ];

        return $ws[$name];
    }
}
