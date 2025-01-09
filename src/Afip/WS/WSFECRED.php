<?php

namespace Cotein\ApiAfip\Afip\WS;

use Cotein\ApiAfip\Constantes;
use Illuminate\Support\Facades\Log;
use SoapClient;
use Exception;

class WSFECRED extends WebService
{
    const SERVICE = 'wsfecred';

    protected $authRequest;

    public function __construct($environment = 'testing', $company_cuit, $company_id, $user_id)
    {
        parent::__construct(self::SERVICE, $environment, $company_cuit, $company_id, $user_id);

        $this->afip_params = [
            'authRequest' => [
                'token' => $this->token,
                'sign' => $this->sign,
                'cuitRepresentada' => $this->cuitRepresentada
            ]

        ];

        $this->connect();
    }

    public function connect(): void
    {
        try {
            $wsdl = strtoupper(self::SERVICE) . '_' . $this->environment;

            $ws = WS_CONST::getWSDL($wsdl);

            $this->soapHttp = new SoapClient($ws);
        } catch (Exception $e) {

            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Consults the obligated reception amount for a given CUIT (tax identification number) and date of issuance.
     *
     * @param string $cuitConsultada The CUIT (tax identification number) to be consulted.
     * @param string $fechaEmision The date of issuance to be consulted.
     * @return mixed The result of the consultation.
     */
    public function consultarMontoObligadoRecepcion($cuitConsultada, $fechaEmision)
    {
        $this->afip_params['cuitConsultada'] = $cuitConsultada;
        $this->afip_params['fechaEmision'] = $fechaEmision;

        return $this->soapHttp->consultarMontoObligadoRecepcion($this->afip_params);
    }

    /**
     * Consultar comprobantes.
     *
     * @param string $rolCUITRepresentada (optional) The role of the CUIT represented. Default is 'Emisor'.
     * Deberá indicar la condición de "Emisor" o "Receptor" en los comprobantes que desea buscar para la <cuitRepresentada> (obtenida del elemento <authRequest>)
     * @param string|null $CUITContraparte (optional) The CUIT of the counterpart.
     * Cuit de la contraparte. Si en <rolCUITRepresentada> indicó Emisor, podrá filtrar por los receptores. Si en <rolCUITRepresentada> indicó Receptor, podrá filtrar por los emisores.
     * @param string|null $codTipoCmp (optional) The code of the type of voucher.
     * @param string|null $estadoCmp (optional) The state of the voucher.
     * @param string|null $fecha (optional) The date of the voucher.
     * @param string|null $codCtaCte (optional) The code of the current account.
     * Código de la Cuenta Corriente sobre la cual se quieren ver los comprobantes. Si ingresa un valor buscará sólo los comprobantes de esa cuenta corriente. Si ingresa el valor 0 la búsqueda no arrojará resultados
     * @param string|null $estadoCtaCte (optional) The state of the current account.
     * @param int|null $nroPagina (optional) The page number.
     *
     * @return void
     */
    public function consultarComprobantes($rolCUITRepresentada = 'Emisor', $CUITContraparte = null, $codTipoCmp = null, $estadoCmp = null, $fecha = null, $codCtaCte = null, $estadoCtaCte = null, $nroPagina = null)
    {

        $this->afip_params['rolCUITRepresentada'] = $rolCUITRepresentada;

        if ($CUITContraparte) $this->afip_params['CUITContraparte'] = $CUITContraparte;
        if ($codTipoCmp) $this->afip_params['codTipoCmp'] = $codTipoCmp;
        if ($estadoCmp) $this->afip_params['estadoCmp'] = $estadoCmp;
        if ($fecha) $this->afip_params['fecha'] = $fecha;
        if ($codCtaCte) $this->afip_params['codCtaCte'] = $codCtaCte;
        if ($estadoCtaCte) $this->afip_params['estadoCtaCte'] = $estadoCtaCte;
        if ($nroPagina) $this->afip_params['nroPagina'] = $nroPagina;

        return $this->soapHttp->consultarComprobantes($this->afip_params);
    }

    public function consultarCuentasEnAgtDptoCltv()
    {
        return $this->soapHttp->consultarCuentasEnAgtDptoCltv($this->afip_params);
    }

    public function Dummy()
    {
        return $this->soapHttp->Dummy();
    }
}
