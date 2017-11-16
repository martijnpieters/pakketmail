<?php

namespace MartijnPieters\PakketMail;

class Shipment
{
    const PAKKETMAIL_PRODUCT_UPS = 10;
    const PAKKETMAIL_PRODUCT_DPD = 13;
    const PAKKETMAIL_PRODUCT_POSTNL_BRIEVENBUSPAKJE = 15;
    const PAKKETMAIL_PRODUCT_POSTNL_PAKKETTEN = 20;

    private static $parameterMapping = [
        'PakketmailProduct' => 'pakketMailProduct',
        'Transporter' => 'transporter',
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
    private $transporter;
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

    /** @var string[][] */
    private $warnings = [];

    /**
     * @param array $properties
     *
     * @throws Exception
     */
    public function __construct(array $properties)
    {
        foreach ($properties as $property => $value) {
            if (property_exists($this, $property)) {
                $this->$property = $value;
            } else {
                throw new Exception(sprintf('Property "%s" does not exist.', $property));
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

    /**
     * @param string[][] $warnings
     */
    public function setWarnings(array $warnings)
    {
        $this->warnings = $warnings;
    }

    /**
     * @return string[][]
     */
    public function getWarnings(): array
    {
        return $this->warnings;
    }

    /**
     * @return bool
     */
    public function hasWarnings(): bool
    {
        return is_array($this->warnings) && count($this->warnings) > 0;
    }

    /**
     * @return mixed
     */
    public function getClientReference()
    {
        return $this->clientReference;
    }
}
