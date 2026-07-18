<?php

declare(strict_types=1);

require dirname(__DIR__) . '/autoload.php';

use Foundwell\Client;
use Foundwell\Config;
use Foundwell\Version;

function smokeAssert(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$config = new Config('https://license.foundwellmedia.com', 'stationos', '0.3.0-alpha');
$client = new Client($config);

smokeAssert($client->sdkVersion() === Version::current(), 'Client and central SDK versions must match.');
smokeAssert($client->sdkVersion() === trim((string) file_get_contents(dirname(__DIR__) . '/VERSION')), 'SDK version must match VERSION.');
smokeAssert($client->config()->product() === 'stationos', 'Product slug should be retained.');
smokeAssert($client->config()->baseUrl() === 'https://license.foundwellmedia.com', 'Base URL should be retained.');

echo "SDK foundation smoke test passed.\n";
