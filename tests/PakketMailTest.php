<?php

namespace MartijnPieters\PakketMail\tests;

use MartijnPieters\PakketMail;
use PHPUnit\Framework\TestCase;

/**
 * @author Martijn Pieters
 */
class PakketMailTest extends TestCase
{
    const API_USERNAME = 'username';
    const API_PASSWORD = 'password';
    const SHIPMENT_PROPERTIES = [
        'clientReference' => 'A123',
        'name1' => 'John Doe',
        'streetName' => 'Foo Street',
        'city' => 'Foo City',
        'postalCode' => '1234 AB',
        'country' => 'NLD',
    ];

    public function testDevelopmentModeInXml()
    {
        $pakketMail = new PakketMail\PakketMail(self::API_USERNAME, self::API_PASSWORD, true);
        $xmlString = $this->invokePrivateMethod($pakketMail, 'generateRequestXml')->asXML();

        $this->assertContains('<Nosave>1</Nosave>', $xmlString);

        var_dump($xmlString);
    }

    public function testProductionModeInXml()
    {
        $pakketMail = new PakketMail\PakketMail(self::API_USERNAME, self::API_PASSWORD, false);
        $xmlString = $this->invokePrivateMethod($pakketMail, 'generateRequestXml')->asXML();

        $this->assertContains('<Nosave>0</Nosave>', $xmlString);
    }

    public function testAddShipment()
    {
        $pakketMail = new PakketMail\PakketMail(self::API_USERNAME, self::API_PASSWORD, true);
        $shipmentA = new PakketMail\Shipment(self::SHIPMENT_PROPERTIES);
        $pakketMail->addShipment($shipmentA);
        $shipmentB = new PakketMail\Shipment(self::SHIPMENT_PROPERTIES);
        $pakketMail->addShipment($shipmentB);

        $this->assertCount(2, $pakketMail->getShipments());
    }

    public function testPostRequest()
    {
        $pakketMail = new PakketMail\PakketMail(self::API_USERNAME, self::API_PASSWORD, true);
        $shipmentA = new PakketMail\Shipment(self::SHIPMENT_PROPERTIES);
        $pakketMail->addShipment($shipmentA);
        $pakketMail->sendPostRequest();
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
