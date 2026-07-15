<?php

declare(strict_types=1);

require dirname(__DIR__) . '/autoload.php';

use Foundwell\Client;
use Foundwell\Config;

$config = new Config('https://license.foundwellmedia.com', 'stationos', '0.1.0-alpha');
$client = new Client($config);

assert($client->sdkVersion() === '0.1.0-alpha');
assert($client->config()->product() === 'stationos');
assert($client->config()->baseUrl() === 'https://license.foundwellmedia.com');

echo "SDK foundation smoke test passed.\n";
