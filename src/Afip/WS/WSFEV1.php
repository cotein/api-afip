<?php

namespace Cotein\ApiAfip\Afip\WS;

use Cotein\ApiAfip\Constantes;
use Illuminate\Support\Facades\Log;


class WSFEV1 extends WebService
{
    const SERVICE = 'wsfe';

    /**
     * Method __construct
     *
     * @param $environment $environment, entorno en AFIP testing ó production
     * @param $cuit=20227339730 $cuit de la empresa que va a realizar la 
     * facturación electrónica que ha delegado el servicio a nombre de DMIT.
     * Por defecto mi CUIT
     *
     * @return void
     */
    public function __construct($environment = 'testing', $company_cuit = Constantes::DIEGO_BARRUETA_CUIT, $company_id, $user_id)
    {
        parent::__construct(self::SERVICE, $environment, $company_cuit, $company_id, $user_id);

        $this->afip_params = [];
        $this->afip_params['token'] = $this->token;
        $this->afip_params['sign'] = $this->sign;
        $this->afip_params['cuitRepresentada'] = $this->cuitRepresentada;

        $this->connect();
    }

    public function connect(): void
    {
        try {

            $wsdl = strtoupper(self::SERVICE) . '_' . $this->environment;

            $ws = WS_CONST::getWSDL($wsdl);

            $this->soapHttp = new \SoapClient(
                $ws,
                [
                    "cache_wsdl" => 0,
                    "connection_timeout" => 5,
                    "exceptions" => true,
                    "features" => 5,
                    "soap_version" => 2,
                    "trace" => true,
                    'stream_context' => stream_context_create(['ssl' => ['ciphers' => 'AES256-SHA', 'verify_peer' => false, 'verify_peer_name' => false]])
                ]
            );

            /* $header = new \SoapHeader('Access-Control-Allow-Origin', '*');

            $this->soapHttp->__setSoapHeaders($header); */
        } catch (\Exception $e) {

            Log::error("Error en try catch WSFEV1" . $e->getMessage() . ' - ' . $e->getCode());

            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }

    public function FEDummy()
    {
        return $this->soapHttp->FEDummy();
    }

    /**
     * Method FEParamGetTiposPaises
     * Método para consultar valores referenciales de códigos de países
     * (FEParamGetTiposPaises)
     * Esta operación permite consultar los códigos de países y descripción de los mismos.
     * @return void
     */
    public function FEParamGetTiposPaises()
    {
        return $this->soapHttp->FEParamGetTiposPaises($this->afip_params);
    }

    public function FEParamGetPtosVenta()
    {
        return $this->soapHttp->FEParamGetPtosVenta($this->afip_params);
    }

    public function FEParamGetTiposCbte()
    {
        return $this->soapHttp->FEParamGetTiposCbte($this->afip_params);
    }

    public function FEParamGetTiposConcepto()
    {
        return $this->soapHttp->FEParamGetTiposConcepto($this->afip_params);
    }

    public function FEParamGetTiposDoc()
    {
        return $this->soapHttp->FEParamGetTiposDoc($this->afip_params);
    }

    public function FEParamGetTiposIva()
    {
        return $this->soapHttp->FEParamGetTiposIva($this->afip_params);
    }

    public function FEParamGetTiposMonedas()
    {
        return $this->soapHttp->FEParamGetTiposMonedas($this->afip_params);
    }

    public function FEParamGetTiposOpcional()
    {
        return $this->soapHttp->FEParamGetTiposOpcional($this->afip_params);
    }

    public function FEParamGetTiposTributos()
    {
        return $this->soapHttp->FEParamGetTiposTributos($this->afip_params);
    }

    public function FEParamGetCotizacion()
    {
        return $this->soapHttp->FEParamGetCotizacion($this->afip_params);
    }

    public function FEParamGetActividades()
    {
        return $this->soapHttp->FEParamGetActividades($this->afip_params);
    }

    public function ConsultarComprobanteEmitido($CbteTipo, $PtoVta, $CbteNro)
    {
        $FeCompConsReq = [
            'CbteTipo' => (int) $CbteTipo,
            'CbteNro' =>  $CbteNro,
            'PtoVta' => (int) $PtoVta,
        ];

        $this->afip_params['FeCompConsReq'] = $FeCompConsReq;

        return $this->soapHttp->FECompConsultar($this->afip_params);
    }

    /**
     * Method FECompUltimoAutorizado
     * Devuelve el número del último comprobante autorizado
     * 
     * @param $CbteTipo $CbteTipo [explicite description]
     * @param $PtoVta $PtoVta [explicite description]
     * @param $CbteNro $CbteNro [explicite description]
     *
     * @return void
     */
    public function FECompUltimoAutorizado($CbteTipo, $PtoVta)
    {

        $this->afip_params['CbteTipo'] = $CbteTipo;
        $this->afip_params['PtoVta'] = $PtoVta;

        return $this->soapHttp->FECompUltimoAutorizado($this->afip_params);
    }

    /**
     * Method FECAESolicitar
     *
     * @param $FeCabReq $FeCabReq array - ver información en AFIP
     * @param $FECAEDetRequest $FECAEDetRequest array - ver información en AFIP
     *
     * @return void
     */
    public function FECAESolicitar($FeCabReq, $FECAEDetRequest)
    {

        $this->afip_params['FeCAEReq'] = [
            'FeCabReq' => $FeCabReq,
            'FeDetReq' => [
                'FECAEDetRequest' => $FECAEDetRequest
            ]
        ];

        return $this->soapHttp->FECAESolicitar($this->afip_params);
    }
}
