<?php

declare(strict_types=1);

require dirname(__DIR__) . '/autoload.php';

use Foundwell\Client;
use Foundwell\Config;

$config = new Config(
    'https://license.foundwellmedia.com',
    'stationos',
    '0.3.0-alpha',
    getenv('FOUNDWELL_API_KEY') ?: null
);

$foundwell = new Client($config);

// Low-level HTTP access is available for SDK service implementations.
// Product code should use higher-level services such as licenses() once added.
$response = $foundwell->http()->request('GET', '/api/v1/status');
var_export($response->json());
