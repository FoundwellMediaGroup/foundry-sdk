<?php

declare(strict_types=1);

namespace Foundwell\Contracts;

interface LicensingInterface
{
    public function activate(string $licenseKey, string $fingerprint, string $hostname): array;
    public function validate(string $licenseKey, string $fingerprint): array;
    public function deactivate(string $licenseKey, string $fingerprint): array;
}
