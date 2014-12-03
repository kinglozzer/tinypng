# TinyPNG PHP API
[![Build Status](https://travis-ci.org/kinglozzer/tinypng.png?branch=master)](https://travis-ci.org/kinglozzer/tinypng)

Provides an easy-to-use API for interacting with TinyPNG's developer API.

## Installation

`composer require kinglozzer/tinypng:0.0.*`

## Usage

```php
use Kinglozzer\TinyPng\Client;
use Kinglozzer\TinyPng\Exception\AuthorizationException;
use Kinglozzer\TinyPng\Exception\InputException;
use Kinglozzer\TinyPng\Exception\LogicException;

$client = new Client('<your-tinypng-api-key>');

try {
    $client->compress('/path/to/original.png');
    $client->compress('<image data>', true); // Compress raw image data
    $client->storeFile('/path/to/compressed.png'); // Write the returned image
    $client->getCompressedFileSize(); // Int size of compressed image, e.g: 104050
    $client->getCompressedFileSize(true); // Human-readable, e.g: '101.61 KB'
    $client->getResponseData(); // array containing JSON-decoded response data
} catch (AuthorizationException $e) {
    // Invalid credentials or requests per month exceeded
} catch (InputException $e) {
    // Not a valid PNG or JPEG
} catch (Exception $e) {
    // Unknown error
}
```
