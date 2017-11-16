<?php

namespace MartijnPieters\PakketMail\tests;

use MartijnPieters\PakketMail;
use PHPUnit\Framework\TestCase;

/**
 * @author Martijn Pieters
 */
class RequestTest extends TestCase
{
    const API_USERNAME = 'username';
    const API_PASSWORD = 'password';
    const DAY_CLOSE_URL = 'https://mijn.pakketmail.nl/zending/dagafsluiting';
    const SHIPMENT_CLIENT_REFERENCE = 'A123';
    const SHIPMENT_PROPERTIES = [
        'pakketMailProduct' => PakketMail\Shipment::PAKKETMAIL_PRODUCT_DPD,
        'clientReference' => self::SHIPMENT_CLIENT_REFERENCE,
        'name1' => 'John Doe',
        'streetName' => 'Main street',
        'city' => 'Foo City',
        'postalCode' => '1234 AB',
        'country' => 'NLD',
    ];

    public function testDevelopmentModeInXml()
    {
        $pakketMailRequest = new PakketMail\Request(self::API_USERNAME, self::API_PASSWORD, true);
        $xmlString = $this->invokePrivateMethod($pakketMailRequest, 'generateRequestXml')->asXML();

        $this->assertContains('<Nosave>1</Nosave>', $xmlString);
    }

    public function testProductionModeInXml()
    {
        $pakketMailRequest = new PakketMail\Request(self::API_USERNAME, self::API_PASSWORD, false);
        $xmlString = $this->invokePrivateMethod($pakketMailRequest, 'generateRequestXml')->asXML();

        $this->assertContains('<Nosave>0</Nosave>', $xmlString);
    }

    public function testAddShipment()
    {
        $pakketMailRequest = new PakketMail\Request(self::API_USERNAME, self::API_PASSWORD, true);
        $shipmentA = new PakketMail\Shipment(self::SHIPMENT_PROPERTIES);
        $pakketMailRequest->addShipment($shipmentA);

        $this->assertCount(1, $pakketMailRequest->getShipments());
    }

    public function testSendToApi()
    {
        $pakketMailRequest = new PakketMail\Request(self::API_USERNAME, self::API_PASSWORD, true);
        $shipmentA = new PakketMail\Shipment(self::SHIPMENT_PROPERTIES);
        $pakketMailRequest->addShipment($shipmentA);
        $response = $pakketMailRequest->sendToApi();
        $shipments = $pakketMailRequest->getShipments();

        $this->assertCount(4, $shipments[self::SHIPMENT_CLIENT_REFERENCE]->getWarnings());
    }

    public function testDayCloseUrl()
    {
        $pakketMailRequest = new PakketMail\Request(self::API_USERNAME, self::API_PASSWORD, true);
        $shipmentA = new PakketMail\Shipment(self::SHIPMENT_PROPERTIES);
        $pakketMailRequest->addShipment($shipmentA);
        $pakketMailRequest->sendToApi();

        $this->assertEquals(self::DAY_CLOSE_URL, $pakketMailRequest->getDayCloseUrl());
    }

    /**
     * @param $object
     * @param string $methodName
     * @param array $parameters
     *
     * @return mixed
     */
    private function invokePrivateMethod(&$object, string $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
