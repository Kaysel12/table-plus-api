<?php

namespace App\Services\Soap;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class SoapTaskClientService
{
    protected Client $client;
    protected string $wsdlUrl;

    public function __construct()
    {
        $this->wsdlUrl = config('services.soap.tasks_wsdl');
        $this->client = new Client([
            'headers' => ['Content-Type' => 'text/xml; charset=utf-8'],
            'verify' => false,
        ]);
    }

    public function sendExportedXml(string $xmlContent)
    {
        try {
            $response = $this->client->post($this->wsdlUrl, [
                'body' => $this->buildSoapEnvelope($xmlContent),
            ]);

            $body = $response->getBody()->getContents();
            return simplexml_load_string($body);
        } catch (RequestException $e) {
            Log::error('SOAP Request Failed', [
                'error' => $e->getMessage(),
                'response' => optional($e->getResponse())->getBody()?->getContents()
            ]);

            throw new \Exception('Error al enviar tareas al servicio SOAP externo');
        }
    }

    private function buildSoapEnvelope(string $xml): string
    {
        return 
        <<<XML
            <?xml version="1.0" encoding="utf-8"?>
            <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:tas="http://example.com/tasks">
            <soapenv:Header/>
            <soapenv:Body>
                <tas:ImportTasks>
                    <tas:xmlData><![CDATA[$xml]]></tas:xmlData>
                </tas:ImportTasks>
            </soapenv:Body>
            </soapenv:Envelope>
        XML;
    }
}
