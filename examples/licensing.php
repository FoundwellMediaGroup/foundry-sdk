<?php

declare(strict_types=1);

require dirname(__DIR__) . '/autoload.php';

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
    __DIR__ . '/../storage/license-cache.json'
);

$identity = new InstallationIdentity(
    'replace-with-a-stable-installation-fingerprint',
    gethostname() ?: 'unknown'
);

$client = new Client($config, null, null, $identity);
$licenseKey = 'replace-with-license-key';

// $activation = $client->licenses()->activate($licenseKey);
// $validation = $client->licenses()->validate($licenseKey);
// $deactivation = $client->licenses()->deactivate($licenseKey);
