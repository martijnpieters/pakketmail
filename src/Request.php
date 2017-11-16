<?php

namespace MartijnPieters\PakketMail;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

/**
 * @author Martijn Pieters
 */
class Request
{
    const PAKKETMAIL_ENDPOINT = 'https://api.pakketmail.nl';
    const PAKKETMAIL_SYSTEM_ID = 19;
    const PAKKETMAIL_API_VERSION = '2.0';
    const PAKKETMAIL_NOSAVE_FALSE = 0;
    const PAKKETMAIL_NOSAVE_TRUE = 1;
    const PAKKETMAIL_XML_REQUEST_HEADER =
        '<?xml version="1.0" encoding="utf-8" ?><PakketMailRequest></PakketMailRequest>';

    /** @var string */
    private $username;

    /** @var string */
    private $password;

    /** @var bool */
    private $developmentMode;

    /** @var Shipment[] */
    private $shipments = [];

    /**
     * @param string $username
     * @param string $password
     * @param bool $developmentMode
     */
    public function __construct(string $username, string $password, bool $developmentMode = false)
    {
        $this->username = $username;
        $this->password = $password;
        $this->developmentMode = $developmentMode;
    }

    /**
     * @param Shipment $shipment
     *
     * @throws Exception
     */
    public function addShipment(Shipment $shipment)
    {
        if (is_null($shipment->getClientReference())) {
            throw new Exception('Client reference is null.');
        } else {
            $this->shipments[$shipment->getClientReference()] = $shipment;
        }
    }

    /**
     * @return Shipment[]
     */
    public function getShipments(): array
    {
        return $this->shipments;
    }

    /**
     * @return Response
     */
    public function sendToApi(): Response
    {
        $client = new Client(
            [
                'base_uri' => self::PAKKETMAIL_ENDPOINT,
                'verify' => false,
            ]
        );

        $response = $client->request(
            'POST',
            '/',
            [
                'headers' => [
                    'Content-Type' => 'text/xml; charset=UTF8',
                ],
                'body' => $this->generateRequestXml()->asXML(),
            ]
        );

        $this->parseResponseStatusCode($response);
        $this->parseResponseErrors($response);
        $this->parseResponseShipments($response);

        return $response;
    }

    /**
     * @return \SimpleXMLElement
     */
    private function generateRequestXml(): \SimpleXMLElement
    {
        $xml = new \SimpleXMLElement(self::PAKKETMAIL_XML_REQUEST_HEADER);
        $xml->addChild('Nosave', $this->determineNoSave());
        $xml->addChild('Version', self::PAKKETMAIL_API_VERSION);
        $xml->addChild('SystemId', self::PAKKETMAIL_SYSTEM_ID);
        $xml->addChild('UserName', $this->username);
        $xml->addChild('Password', $this->password);

        $this->generateRequestXmlShipments($xml);

        return $xml;
    }

    /**
     * @return int
     */
    private function determineNoSave(): int
    {
        if ($this->developmentMode === true) {
            return self::PAKKETMAIL_NOSAVE_TRUE;
        } else {
            return self::PAKKETMAIL_NOSAVE_FALSE;
        }
    }

    /**
     * @param \SimpleXMLElement $xml
     */
    private function generateRequestXmlShipments(\SimpleXMLElement $xml)
    {
        if (is_array($this->shipments)) {
            $xmlShipments = $xml->addChild('CreateShipment');

            foreach ($this->shipments as $shipment) {
                $shipmentListDom = dom_import_simplexml($xmlShipments);
                $shipmentDom = dom_import_simplexml($shipment->getXml());
                $shipmentListDom->appendChild($shipmentListDom->ownerDocument->importNode($shipmentDom, true));
            }
        }
    }

    /**
     * @param Response $response
     *
     * @throws Exception
     */
    private function parseResponseStatusCode(Response $response)
    {
        if ($response->getStatusCode() !== 200) {
            throw new Exception(sprintf('Unexpected status code "%s".', $response->getStatusCode()));
        }
    }

    /**
     * @param Response $response
     */
    private function parseResponseErrors(Response $response)
    {
        $responseArray = $this->getResponseArray($response);
        $this->parseErrors($responseArray);
    }

    /**
     * @param Response $response
     */
    private function parseResponseShipments(Response $response)
    {
        $responseArray = $this->getResponseArray($response);

        if (isset($responseArray['Shipments']['Shipment']) && is_array($responseArray['Shipments']['Shipment'])) {
            if (isset($responseArray['Shipments']['Shipment']['ClientReference'])) {
                $shipment = $responseArray['Shipments']['Shipment'];

                $this->parseErrors($shipment);
                $this->parseWarnings($shipment);
            } else {
                foreach ($responseArray['Shipments']['Shipment'] as $shipment) {
                    $this->parseErrors($shipment);
                    $this->parseWarnings($shipment);
                }
            }
        }
    }

    /**
     * @param Response $response
     *
     * @return mixed[]
     */
    private function getResponseArray(Response $response): array
    {
        $contents = (string)$response->getBody();
        $xml = simplexml_load_string($contents);
        $json = json_encode($xml);

        return json_decode($json, true);
    }

    /**
     * @param string[] $shipment
     *
     * @throws Exception
     */
    private function parseErrors(array $shipment)
    {
        if (isset($shipment['ErrorList'], $shipment['ErrorList']['Error'])) {
            $error = $shipment['ErrorList']['Error'];

            throw new Exception(
                vsprintf(
                    'PakketMail error %s: "%s".',
                    [
                        $error['ErrorCode'],
                        $error['ErrorMessage'],
                    ]
                ),
                $error['ErrorCode']
            );
        }
    }

    /**
     * @param string[] $shipment
     *
     * @throws Exception
     */
    private function parseWarnings(array $shipment)
    {
        if (isset($shipment['WarningList'], $shipment['WarningList']['Warning']) &&
            is_array($shipment['WarningList']['Warning'])
        ) {
            $clientReference = $shipment['ClientReference'];

            if (isset($this->shipments[$clientReference])) {
                $this->shipments[$clientReference]->setWarnings($shipment['WarningList']['Warning']);
            } else {
                throw new Exception(sprintf('Cannot find shipment with reference "%s".', $clientReference));
            }
        }
    }
}
