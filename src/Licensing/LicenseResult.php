<?php

declare(strict_types=1);

namespace Foundwell\Licensing;

final class LicenseResult
{
    private string $status;
    private bool $valid;
    private ?string $activationId;
    private ?string $organization;
    private ?string $product;
    private ?string $licenseType;
    private ?string $expiresAt;
    private int $gracePeriodDays;
    private ?string $nextCheckInAt;
    private ?string $requestId;
    private bool $fromCache;
    /** @var array<string,mixed> */
    private array $raw;

    /** @param array<string,mixed> $raw */
    public function __construct(
        string $status,
        bool $valid,
        ?string $activationId = null,
        ?string $organization = null,
        ?string $product = null,
        ?string $licenseType = null,
        ?string $expiresAt = null,
        int $gracePeriodDays = 0,
        ?string $nextCheckInAt = null,
        ?string $requestId = null,
        bool $fromCache = false,
        array $raw = []
    ) {
        $this->status = $status;
        $this->valid = $valid;
        $this->activationId = $activationId;
        $this->organization = $organization;
        $this->product = $product;
        $this->licenseType = $licenseType;
        $this->expiresAt = $expiresAt;
        $this->gracePeriodDays = max(0, $gracePeriodDays);
        $this->nextCheckInAt = $nextCheckInAt;
        $this->requestId = $requestId;
        $this->fromCache = $fromCache;
        $this->raw = $raw;
    }

    /** @param array<string,mixed> $payload */
    public static function fromPayload(array $payload, bool $fromCache = false): self
    {
        $status = (string) ($payload['status'] ?? 'unknown');
        $valid = isset($payload['valid']) ? (bool) $payload['valid'] : in_array($status, ['active', 'deactivated'], true);
        return new self(
            $status,
            $valid,
            isset($payload['activation_id']) ? (string) $payload['activation_id'] : null,
            isset($payload['organization']) ? (string) $payload['organization'] : null,
            isset($payload['product']) ? (string) $payload['product'] : null,
            isset($payload['license_type']) ? (string) $payload['license_type'] : null,
            isset($payload['expires_at']) && $payload['expires_at'] !== null ? (string) $payload['expires_at'] : null,
            (int) ($payload['grace_period_days'] ?? 0),
            isset($payload['next_check_in_at']) ? (string) $payload['next_check_in_at'] : null,
            isset($payload['request_id']) ? (string) $payload['request_id'] : null,
            $fromCache,
            $payload
        );
    }

    public function status(): string { return $this->status; }
    public function isValid(): bool { return $this->valid; }
    public function activationId(): ?string { return $this->activationId; }
    public function organization(): ?string { return $this->organization; }
    public function product(): ?string { return $this->product; }
    public function licenseType(): ?string { return $this->licenseType; }
    public function expiresAt(): ?string { return $this->expiresAt; }
    public function gracePeriodDays(): int { return $this->gracePeriodDays; }
    public function nextCheckInAt(): ?string { return $this->nextCheckInAt; }
    public function requestId(): ?string { return $this->requestId; }
    public function fromCache(): bool { return $this->fromCache; }
    /** @return array<string,mixed> */
    public function raw(): array { return $this->raw; }
}
