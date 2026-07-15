# Foundwell SDK for PHP

Official PHP SDK for connecting Foundwell applications to the Foundwell Platform.

## Status

**Version:** 0.1.0-alpha  
**Phase:** Foundation only

This release establishes the SDK's public namespace, configuration model, contracts, exception hierarchy, autoloading, and repository conventions. It intentionally does not perform network requests yet.

## Requirements

- PHP 7.4 or newer
- No framework required
- Composer optional

## Quick Start

```php
require __DIR__ . '/autoload.php';

use Foundwell\Client;
use Foundwell\Config;

$config = new Config(
    'https://license.foundwellmedia.com',
    'stationos',
    '0.1.0-alpha'
);

$client = new Client($config);
```

## Scope

The SDK will eventually provide reusable clients for licensing, updates, downloads, health reporting, support, and telemetry.

It will not contain StationOS-specific business logic, customer portal UI, platform administration, or database migrations.

## Roadmap

- **0.1.x:** Foundation, contracts, configuration, exceptions
- **0.2.x:** HTTP transport, authentication, retries, logging
- **0.3.x:** Licensing and offline validation cache
- **0.4.x:** Updates, downloads, and release manifests
- **0.5.x:** Health, telemetry, and support services

## Testing

```bash
php tests/smoke.php
```
