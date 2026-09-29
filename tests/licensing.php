<?php

declare(strict_types=1);

require dirname(__DIR__) . '/autoload.php';
require __DIR__ . '/Fixtures/FakeTransport.php';

use Foundwell\Client;
use Foundwell\Config;
use Foundwell\Exceptions\ActivationLimitException;
use Foundwell\Exceptions\ConnectionException;
use Foundwell\Http\Response;
use Foundwell\Licensing\InstallationIdentity;
use Foundwell\Tests\Fixtures\FakeTransport;

function licensingAssert(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$cache = sys_get_temp_dir() . '/foundwell-sdk-license-' . bin2hex(random_bytes(4)) . '.json';
$config = new Config('https://license.foundwellmedia.com', 'stationos', '0.3.0-alpha', null, 5, 15, 1, 0, $cache);
$identity = new InstallationIdentity('test-installation-fingerprint-0001', 'station.example.com');
$transport = new FakeTransport([
    new Response(200, ['X-Request-ID' => 'activation-http'], json_encode([
        'status' => 'active',
        'activation_id' => 'activation-uuid',
        'organization' => 'TrekRadio',
        'product' => 'Foundry',
        'license_type' => 'professional',
        'expires_at' => null,
        'grace_period_days' => 14,
        'next_check_in_at' => gmdate('c', time() + 86400),
        'request_id' => 'activation-body',
    ], JSON_UNESCAPED_SLASHES)),
]);
$client = new Client($config, $transport, null, $identity);
$result = $client->licenses()->activate('FW-TEST-LICENSE');
licensingAssert($result->isValid(), 'Activation should be valid.');
licensingAssert($result->activationId() === 'activation-uuid', 'Activation ID should be mapped.');
licensingAssert($result->licenseType() === 'professional', 'License type should be mapped.');
licensingAssert(is_file($cache), 'Successful activation should create offline cache.');
$request = $transport->requests()[0];
$body = json_decode((string) $request->body(), true);
licensingAssert($body['product'] === 'stationos', 'Product slug should come from configuration.');
licensingAssert($body['fingerprint'] === $identity->fingerprint(), 'Fingerprint should be sent.');
licensingAssert($body['hostname'] === 'station.example.com', 'Hostname should be sent.');
licensingAssert($body['version'] === '0.3.0-alpha', 'Product version should be sent.');

$offlineTransport = new class implements \Foundwell\Contracts\TransportInterface {
    public function send(\Foundwell\Http\Request $request): \Foundwell\Http\Response
    {
        throw new ConnectionException('Platform unavailable.');
    }
};
$offlineClient = new Client($config, $offlineTransport, null, $identity);
$cached = $offlineClient->licenses()->validate('FW-TEST-LICENSE');
licensingAssert($cached->isValid(), 'Cached validation should remain valid during grace.');
licensingAssert($cached->fromCache(), 'Offline validation should identify cached result.');

$validationTransport = new FakeTransport([
    new Response(200, ['X-Request-ID' => 'validation-http'], '{"status":"active","valid":true,"activation_id":"activation-uuid"}'),
]);
(new Client($config, $validationTransport, null, $identity))->licenses()->validate('FW-TEST-LICENSE');
$validationBody = json_decode((string) $validationTransport->requests()[0]->body(), true);
licensingAssert(($validationBody['hostname'] ?? null) === 'station.example.com', 'Validation should refresh the public installation hostname.');


$suspendedTransport = new FakeTransport([
    new Response(403, ['X-Request-ID' => 'suspended-ref'], json_encode([
        'status' => 'suspended',
        'valid' => false,
        'message' => 'This license has been suspended.',
        'request_id' => 'suspended-body',
    ], JSON_UNESCAPED_SLASHES)),
]);
$suspended = (new Client($config, $suspendedTransport, null, $identity))->licenses()->validate('FW-TEST-LICENSE');
licensingAssert(!$suspended->isValid(), 'Suspended validation should be invalid.');
licensingAssert($suspended->status() === 'suspended', 'Suspended status should be preserved from a 403 response.');
licensingAssert(($suspended->raw()['message'] ?? null) === 'This license has been suspended.', 'Suspended message should be preserved.');

$limitTransport = new FakeTransport([
    new Response(409, ['X-Request-ID' => 'limit-ref'], '{"status":"limit_reached","message":"Activation limit reached"}'),
]);
try {
    (new Client($config, $limitTransport, null, $identity))->licenses()->activate('FW-TEST-LICENSE');
    throw new RuntimeException('Expected activation limit exception.');
} catch (ActivationLimitException $exception) {
    licensingAssert($exception->requestId() === 'limit-ref', 'Activation limit exception should include request ID.');
}

@unlink($cache);
echo "Licensing tests passed.\n";
