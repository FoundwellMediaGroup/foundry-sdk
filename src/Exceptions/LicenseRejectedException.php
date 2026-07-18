<?php

declare(strict_types=1);

namespace Foundwell\Exceptions;

class LicenseRejectedException extends LicensingException
{
    private ?string $requestId;
    /** @var array<string,mixed> */
    private array $payload;

    /** @param array<string,mixed> $payload */
    public function __construct(string $message, int $code = 0, ?string $requestId = null, ?\Throwable $previous = null, array $payload = [])
    {
        parent::__construct($message, $code, $previous);
        $this->requestId = $requestId;
        $this->payload = $payload;
    }

    public function requestId(): ?string
    {
        return $this->requestId;
    }

    /** @return array<string,mixed> */
    public function payload(): array
    {
        return $this->payload;
    }
}
