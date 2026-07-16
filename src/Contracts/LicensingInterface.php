<?php

declare(strict_types=1);

namespace Foundwell\Contracts;

use Foundwell\Licensing\LicenseResult;

interface LicensingInterface
{
    public function activate(string $licenseKey, ?string $fingerprint = null, ?string $hostname = null): LicenseResult;
    public function validate(string $licenseKey, ?string $fingerprint = null): LicenseResult;
    public function deactivate(string $licenseKey, ?string $fingerprint = null): LicenseResult;
}
