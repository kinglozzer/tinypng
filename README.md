# TinyPNG PHP API
[![Build Status](https://travis-ci.org/kinglozzer/tinypng.png?branch=master)](https://travis-ci.org/kinglozzer/tinypng)

Provides an easy-to-use API for interacting with TinyPNG's developer API.

## Installation

`composer require kinglozzer/tinypng:0.1.*`

## Usage

```php
use Kinglozzer\TinyPng\Compressor;
use Kinglozzer\TinyPng\Exception\AuthorizationException;
use Kinglozzer\TinyPng\Exception\InputException;
use Kinglozzer\TinyPng\Exception\LogicException;

$compressor = new Compressor('<your-tinypng-api-key>');

try {
    $result = $compressor->compress('/path/to/original.png');
    $result = $compressor->compress('<image data>', true); // Compress raw image data
    $result->writeTo('/path/to/compressed.png'); // Write the returned image
    $result->getCompressedFileSize(); // Int size of compressed image, e.g: 104050
    $result->getCompressedFileSize(true); // Human-readable, e.g: '101.61 KB'
    $result->getResponseData(); // array containing JSON-decoded response data
} catch (AuthorizationException $e) {
    // Invalid credentials or requests per month exceeded
} catch (InputException $e) {
    // Not a valid PNG or JPEG
} catch (Exception $e) {
    // Unknown error
}
```
