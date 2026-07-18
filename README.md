# Foundwell SDK for PHP

Official dependency-light PHP SDK for connecting Foundwell products to Foundwell Platform services.

**Version:** 0.3.2-alpha  
**PHP:** 7.4 or newer  
**Runtime dependencies:** PHP and the cURL extension for live requests

## Current capabilities

- Validated SDK configuration
- Product and SDK identification headers
- Replaceable HTTP transport
- JSON request and response handling
- Bounded retries for transient failures
- Request-ID-aware exceptions
- License activation, validation, and deactivation
- Structured license rejection states
- Stable installation identity support
- Integrity-protected offline license cache
- Server-controlled offline grace periods

Contracts for updates, downloads, health, support, and telemetry are retained as planned public boundaries for later SDK capabilities.

## Installation without Composer

Copy the repository into the product and load its autoloader:

```php
require '/path/to/foundwell-sdk-php/autoload.php';
```

## Composer-compatible installation

```bash
composer require foundwell/sdk-php
```

The package is private and is not currently published to Packagist.

## Licensing example

```php
use Foundwell\Client;
use Foundwell\Config;
use Foundwell\Licensing\InstallationIdentity;

$config = new Config(
    'https://license.foundwellmedia.com',
    'stationos',
    '0.3.0-alpha',
    null,
    5,
    15,
    3,
    200,
    '/var/lib/stationos/foundwell-license.json'
);

$identity = new InstallationIdentity($stableFingerprint, gethostname() ?: 'unknown');
$foundwell = new Client($config, null, null, $identity);

$activation = $foundwell->licenses()->activate($licenseKey);
$validation = $foundwell->licenses()->validate($licenseKey);
$deactivation = $foundwell->licenses()->deactivate($licenseKey);
```

Successful activation and validation responses are cached with an integrity signature. The cache is used only when Foundwell Platform cannot be reached and only while the server-provided grace period remains valid. Explicit rejection states such as suspended, revoked, expired, or not activated are never overridden by cached data.

## Tests

```bash
php tests/smoke.php
php tests/networking.php
php tests/licensing.php
php tests/versioning.php
```

The test suite uses an in-memory fake transport and does not contact Foundwell Platform.
