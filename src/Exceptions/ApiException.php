<?php

declare(strict_types=1);

namespace Foundwell\Exceptions;

class ApiException extends FoundwellException
{
    private ?string $requestId;

    public function __construct(string $message, int $code = 0, ?string $requestId = null, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->requestId = $requestId;
    }

    public function requestId(): ?string
    {
        return $this->requestId;
    }
}
