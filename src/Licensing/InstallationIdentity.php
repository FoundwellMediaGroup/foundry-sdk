<?php

declare(strict_types=1);

namespace Foundwell\Licensing;

use Foundwell\Exceptions\ValidationException;

final class InstallationIdentity
{
    private string $fingerprint;
    private string $hostname;

    public function __construct(string $fingerprint, ?string $hostname = null)
    {
        $fingerprint = trim($fingerprint);
        if ($fingerprint === '') {
            throw new ValidationException('A stable installation fingerprint is required.');
        }
        if (strlen($fingerprint) < 16) {
            throw new ValidationException('The installation fingerprint must contain at least 16 characters.');
        }
        $this->fingerprint = $fingerprint;
        $resolvedHostname = trim((string) $hostname);
        if ($resolvedHostname === '') {
            $resolvedHostname = (string) gethostname();
        }
        $this->hostname = $resolvedHostname !== '' ? $resolvedHostname : 'unknown';
    }

    public function fingerprint(): string { return $this->fingerprint; }
    public function hostname(): string { return $this->hostname; }
}
