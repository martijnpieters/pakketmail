<?php

namespace MartijnPieters\PakketMail;

use GuzzleHttp\Client;

/**
 * @author Martijn Pieters
 */
class PakketMail
{
    const PAKKETMAIL_ENDPOINT = 'https://api.pakketmail.nl';
    const PAKKETMAIL_SYSTEM_ID = 19;
    const PAKKETMAIL_API_VERSION = '2.0';
    const PAKKETMAIL_NOSAVE_FALSE = 0;
    const PAKKETMAIL_NOSAVE_TRUE = 1;
    const XML_REQUEST_HEADER = '<?xml version="1.0" encoding="utf-8" ?><PakketMailRequest></PakketMailRequest>';

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
     */
    public function addShipment(Shipment $shipment)
    {
        $this->shipments[] = $shipment;
    }

    /**
     * @return Shipment[]
     */
    public function getShipments(): array
    {
        return $this->shipments;
    }

    /**
     *
     */
    public function sendPostRequest()
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

        var_dump($response->getBody());
    }

    /**
     * @return \SimpleXMLElement
     */
    private function generateRequestXml(): \SimpleXMLElement
    {
        $xml = new \SimpleXMLElement(self::XML_REQUEST_HEADER);
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
     * @param string $xmlString
     *
     * @return array
     */
    private function xmlStringToArray(string $xmlString): array
    {
        $xml = simplexml_load_string($xmlString);
        $json = json_encode($xml);

        return json_decode($json, true);
    }
}
