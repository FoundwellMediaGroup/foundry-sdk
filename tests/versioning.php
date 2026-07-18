<?php

declare(strict_types=1);

require dirname(__DIR__) . '/autoload.php';
require __DIR__ . '/Fixtures/FakeTransport.php';

use Foundwell\Client;
use Foundwell\Config;
use Foundwell\Http\Response;
use Foundwell\Tests\Fixtures\FakeTransport;
use Foundwell\Version;

function versionAssert(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$config = new Config('https://license.foundwellmedia.com', 'stationos', '0.3.0-alpha');
$transport = new FakeTransport([
    new Response(200, [], '{"status":"ok"}'),
]);
$client = new Client($config, $transport);
$client->http()->request('GET', '/api/v1/status');

$request = $transport->requests()[0];
$userAgent = $request->headers()['User-Agent'] ?? '';
versionAssert($client->sdkVersion() === Version::current(), 'Client must use the central SDK version.');
versionAssert(strpos($userAgent, 'Foundwell-SDK-PHP/' . Version::current()) === 0, 'User-Agent must use the central SDK version.');

echo "SDK versioning tests passed.\n";
