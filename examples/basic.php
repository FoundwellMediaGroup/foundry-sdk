<?php

declare(strict_types=1);

require dirname(__DIR__) . '/autoload.php';

use Foundwell\Client;
use Foundwell\Config;

$config = new Config(
    'https://license.foundwellmedia.com',
    'stationos',
    '0.1.0-alpha'
);

$client = new Client($config);

echo 'Foundwell SDK ' . $client->sdkVersion() . PHP_EOL;
echo 'Product: ' . $client->config()->product() . PHP_EOL;
