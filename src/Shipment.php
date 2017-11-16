<?php

namespace MartijnPieters\PakketMail;

class Shipment
{
    private static $parameterMapping = [
        'PakketMailProduct' => 'pakketMailProduct',
        'ClientSub' => 'clientSub',
        'ClientReference' => 'clientReference',
        'Name1' => 'name1',
        'Name2' => 'name2',
        'Streetname' => 'streetName',
        'Streetname2' => 'streetName2',
        'Number' => 'number',
        'NumberExtension' => 'numberExtension',
        'City' => 'city',
        'Postalcode' => 'postalCode',
        'Country' => 'country',
        'Province' => 'province',
        'Phone' => 'phone',
        'Email' => 'email',
        'Weight' => 'weight',
        'Width' => 'width',
        'Height' => 'height',
        'Length' => 'length',
        'CustomsType' => 'customsType',
        'Value' => 'value',
        'Description' => 'description',
        'HSCode' => 'hsCode',
    ];

    private $pakketMailProduct;
    private $clientSub;
    private $clientReference;
    private $name1;
    private $name2;
    private $streetName;
    private $streetName2;
    private $number;
    private $numberExtension;
    private $city;
    private $postalCode;
    private $country;
    private $province;
    private $phone;
    private $email;
    private $weight;
    private $width;
    private $height;
    private $length;
    private $customsType;
    private $value;
    private $description;
    private $hsCode;

    /**
     * @param array $properties
     *
     * @throws \Exception
     */
    public function __construct(array $properties)
    {
        foreach ($properties as $property => $value) {
            if (property_exists($this, $property)) {
                $this->$property = $value;
            } else {
                throw new \Exception(sprintf('Property "%s" does not exist.', $property));
            }
        }
    }

    /**
     * @return \SimpleXMLElement
     */
    public function getXml(): \SimpleXMLElement
    {
        $xml = new \SimpleXMLElement('<Shipment/>');

        foreach (self::$parameterMapping as $key => $property) {
            $value = $this->$property;

            if (!is_null($value)) {
                $xml->addChild($key, $value);
            }
        }

        return $xml;
    }
}
