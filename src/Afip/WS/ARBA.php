<?php

namespace Cotein\ApiAfip\Afip\WS;

use Carbon\Carbon;
use SimpleXMLElement;
use Exception;

class ARBA
{
    private const ARBA_URL = 'https://dfe.arba.gov.ar/DomicilioElectronico/SeguridadCliente/dfeServicioConsulta.do';

    protected string $path;

    public function __construct()
    {
        $this->path = dirname(__FILE__);
    }

    public function xml_create(string $cuit): string
    {
        $ruta =  WS_CONST::DFESERVICIO_CONSULTA_CON_CUIT;

        $xml = simplexml_load_file(WS_CONST::DFESERVICIO_CONSULTA . '.xml');

        if ($xml === false) {
            throw new Exception('Error loading XML file.');
        }

        $xml->fechaDesde = $this->FirstDayActualMonth();
        $xml->fechaHasta = $this->LastDayActualMonth();
        $xml->contribuyentes->contribuyente->cuitContribuyente = $cuit;
        $xml->asXml($ruta);

        $md5 = md5_file($ruta);
        $newFileName =  WS_CONST::DFESERVICIO_CONSULTA . '_' . $md5 . '.xml';
        rename($ruta, $newFileName);

        return $newFileName;
    }

    public function alicuota_sujeto(string $cuit): SimpleXMLElement
    {
        $xml_file = $this->xml_create($cuit);
        $data = [
            'user'     => env('ARBA_CUIT'),
            'password' => env('ARBA_CLAVE'),
            'file'     => curl_file_create($xml_file)
        ];
        $header = ['Content-Type: multipart/form-data'];

        $result = $this->makeCurlRequest(self::ARBA_URL, $data, $header);

        $xml = new SimpleXMLElement($result);

        // Eliminar el archivo XML después de usarlo
        $this->deleteXmlFile($xml_file);

        return $xml;
    }

    private function makeCurlRequest(string $url, array $data, array $header): string
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 4);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

        $result = curl_exec($ch);

        if ($result === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception('cURL Error: ' . $error);
        }

        curl_close($ch);
        return $result;
    }

    private function FirstDayActualMonth(): string
    {
        return Carbon::now()->firstOfMonth()->format('Ymd');
    }

    private function LastDayActualMonth(): string
    {
        return Carbon::now()->lastOfMonth()->format('Ymd');
    }

    private function deleteXmlFile(string $filePath): void
    {
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
}
