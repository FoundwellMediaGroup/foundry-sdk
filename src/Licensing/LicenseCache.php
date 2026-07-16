<?php

declare(strict_types=1);

namespace Foundwell\Licensing;

use Foundwell\Exceptions\LicensingException;

final class LicenseCache
{
    private ?string $path;

    public function __construct(?string $path)
    {
        $path = $path !== null ? trim($path) : null;
        $this->path = $path !== '' ? $path : null;
    }

    public function enabled(): bool { return $this->path !== null; }

    /** @param array<string,mixed> $payload */
    public function store(array $payload, string $licenseKey, string $fingerprint): void
    {
        if ($this->path === null) {
            return;
        }
        $directory = dirname($this->path);
        if (!is_dir($directory) && !@mkdir($directory, 0700, true) && !is_dir($directory)) {
            throw new LicensingException('Unable to create the offline license cache directory.');
        }
        $record = [
            'cached_at' => gmdate('c'),
            'payload' => $payload,
        ];
        $record['signature'] = $this->signature($record['cached_at'], $payload, $licenseKey, $fingerprint);
        $json = json_encode($record, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        if ($json === false) {
            throw new LicensingException('Unable to encode the offline license cache.');
        }
        $temporary = $this->path . '.tmp.' . bin2hex(random_bytes(4));
        if (@file_put_contents($temporary, $json, LOCK_EX) === false) {
            throw new LicensingException('Unable to write the offline license cache.');
        }
        @chmod($temporary, 0600);
        if (!@rename($temporary, $this->path)) {
            @unlink($temporary);
            throw new LicensingException('Unable to finalize the offline license cache.');
        }
    }

    public function load(string $licenseKey, string $fingerprint): ?LicenseResult
    {
        if ($this->path === null || !is_file($this->path)) {
            return null;
        }
        $raw = @file_get_contents($this->path);
        if ($raw === false) {
            return null;
        }
        $record = json_decode($raw, true);
        if (!is_array($record) || !isset($record['cached_at'], $record['payload'], $record['signature']) || !is_array($record['payload'])) {
            return null;
        }
        $expected = $this->signature((string) $record['cached_at'], $record['payload'], $licenseKey, $fingerprint);
        if (!hash_equals($expected, (string) $record['signature'])) {
            return null;
        }
        $payload = $record['payload'];
        $result = LicenseResult::fromPayload($payload, true);
        if (!$result->isValid() || $result->status() !== 'active') {
            return null;
        }
        $cachedAt = strtotime((string) $record['cached_at']);
        if ($cachedAt === false) {
            return null;
        }
        $graceSeconds = $result->gracePeriodDays() * 86400;
        if ($graceSeconds < 1 || time() > ($cachedAt + $graceSeconds)) {
            return null;
        }
        if ($result->expiresAt() !== null) {
            $expiresAt = strtotime($result->expiresAt());
            if ($expiresAt !== false && time() >= $expiresAt) {
                return null;
            }
        }
        return $result;
    }

    /** @param array<string,mixed> $payload */
    private function signature(string $cachedAt, array $payload, string $licenseKey, string $fingerprint): string
    {
        $json = json_encode($payload, JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new LicensingException('Unable to sign the offline license cache.');
        }
        $secret = hash('sha256', $licenseKey . "\0" . $fingerprint, true);
        return hash_hmac('sha256', $cachedAt . "\0" . $json, $secret);
    }
}
