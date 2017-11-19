# PakketMail SDK (PHP 7.0+)

## Requirements

- [PakketMail](http://www.pakketmail.nl/) account
- PHP >= 7.0
- PHP cURL extension

## Installation

The easiest way to install the PakketMail SDK is to require it with [Composer](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx).

```bash
$ composer require martijnpieters/pakketmail:dev-master
```

## Getting started

Require the Composer autoloader and include the PakketMail namespace.

```php
require 'vendor/autoload.php';

use MartijnPieters\PakketMail;
```

Initialize a request with your username and password, and optionally the development flag `true`.

```php
$pakketMailRequest = new PakketMail\Request('username', 'password', true);
```

Create a new parcel and add it to the request.

```php
$shipmentProperties = [
    'pakketMailProduct' => PakketMail\Shipment::PAKKETMAIL_PRODUCT_DPD,
    'clientReference' => 'A123',
    'name1' => 'John Doe',
    'streetName' => 'Main street',
    'city' => 'Foo City',
    'postalCode' => '1234 AB',
    'country' => 'NLD',
];
$shipment = new PakketMail\Shipment($shipmentProperties);

$pakketMailRequest->addShipment($shipment);
```

Execute the request and retrieve the day close URL (`dagafsluiting`).

```php
$pakketMailRequest->sendToApi();

print($pakketMailRequest->getDayCloseUrl());
```

## Exceptions

Catch exceptions during the API call.

```php
try {
    $pakketMailRequest->sendToApi();
} catch (PakketMail\Exception $e) {
    throw new Exception($e->getMessage());
}
```
