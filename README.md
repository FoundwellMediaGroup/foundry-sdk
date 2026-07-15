# Foundwell SDK for PHP

Official PHP SDK for connecting Foundwell applications to the Foundwell Platform.

**Version:** 0.2.0-alpha  
**PHP:** 7.4 or newer  
**Dependencies:** none beyond PHP and the cURL extension for live requests

## Current scope

This release provides the shared networking layer used by future capability services:

- validated configuration
- immutable requests and responses
- cURL transport behind a replaceable interface
- bearer API-key authentication
- product and SDK identification headers
- JSON request and response handling
- bounded retries for transient failures
- configurable timeouts
- logger hooks
- request-ID-aware exceptions

Licensing, updates, downloads, health, support, and telemetry services remain contracts only. Licensing implementation is planned for v0.3.0-alpha.

## Installation without Composer

Copy the repository into the product and load:

```php
require '/path/to/foundwell-sdk-php/autoload.php';
```

## Composer-compatible installation

```bash
composer require foundwell/sdk-php
```

The package is currently private and is not published to Packagist.

## Configuration

```php
use Foundwell\Client;
use Foundwell\Config;

$config = new Config(
    'https://license.foundwellmedia.com',
    'stationos',
    '0.1.0-alpha',
    getenv('FOUNDWELL_API_KEY') ?: null
);

$foundwell = new Client($config);
```

## Tests

```bash
php tests/smoke.php
php tests/networking.php
```

The networking tests use an in-memory fake transport and do not contact the live Platform.
