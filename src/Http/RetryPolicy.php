<?php

declare(strict_types=1);

namespace Foundwell\Http;

final class RetryPolicy
{
    private int $maxAttempts;
    private int $baseDelayMilliseconds;

    public function __construct(int $maxAttempts = 3, int $baseDelayMilliseconds = 200)
    {
        $this->maxAttempts = max(1, $maxAttempts);
        $this->baseDelayMilliseconds = max(0, $baseDelayMilliseconds);
    }

    public function maxAttempts(): int { return $this->maxAttempts; }

    public function shouldRetryStatus(int $statusCode): bool
    {
        return in_array($statusCode, [408, 425, 429, 500, 502, 503, 504], true);
    }

    public function delayMilliseconds(int $attempt): int
    {
        return $this->baseDelayMilliseconds * (2 ** max(0, $attempt - 1));
    }
}
