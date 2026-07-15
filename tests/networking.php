<?php

declare(strict_types=1);

require dirname(__DIR__) . '/autoload.php';
require __DIR__ . '/Fixtures/FakeTransport.php';

use Foundwell\Client;
use Foundwell\Config;
use Foundwell\Exceptions\AuthenticationException;
use Foundwell\Http\Response;
use Foundwell\Tests\Fixtures\FakeTransport;

function assertTrue(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$config = new Config('https://license.foundwellmedia.com', 'stationos', '0.1.0-alpha', 'secret', 5, 15, 3, 0);
$transport = new FakeTransport([
    new Response(503, ['X-Request-ID' => 'retry-one'], '{"error":"busy"}'),
    new Response(200, ['X-Request-ID' => 'success-one'], '{"status":"ok"}'),
]);
$client = new Client($config, $transport);
$response = $client->http()->request('POST', '/api/v1/test', ['hello' => 'world']);
assertTrue($response->json()['status'] === 'ok', 'Expected successful decoded response.');
assertTrue(count($transport->requests()) === 2, 'Expected retry after HTTP 503.');
$request = $transport->requests()[0];
assertTrue($request->headers()['Authorization'] === 'Bearer secret', 'Expected bearer authentication header.');
assertTrue($request->headers()['X-Foundwell-Product'] === 'stationos', 'Expected product header.');
assertTrue($request->body() === '{"hello":"world"}', 'Expected JSON request body.');

$authTransport = new FakeTransport([
    new Response(401, ['X-Request-ID' => 'auth-ref'], '{"message":"Invalid credentials"}'),
]);
try {
    (new Client($config, $authTransport))->http()->request('GET', '/private');
    throw new RuntimeException('Expected authentication exception.');
} catch (AuthenticationException $exception) {
    assertTrue($exception->requestId() === 'auth-ref', 'Expected request ID on exception.');
}

echo "Networking tests passed.\n";
