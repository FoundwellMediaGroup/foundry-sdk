<?php

declare(strict_types=1);

namespace Foundwell\Http;

use Foundwell\Exceptions\ApiException;

final class Response
{
    private int $statusCode;
    /** @var array<string,string> */
    private array $headers;
    private string $body;

    /** @param array<string,string> $headers */
    public function __construct(int $statusCode, array $headers, string $body)
    {
        $this->statusCode = $statusCode;
        $this->headers = $headers;
        $this->body = $body;
    }

    public function statusCode(): int { return $this->statusCode; }
    /** @return array<string,string> */
    public function headers(): array { return $this->headers; }
    public function body(): string { return $this->body; }
    public function isSuccessful(): bool { return $this->statusCode >= 200 && $this->statusCode < 300; }

    public function header(string $name): ?string
    {
        $needle = strtolower($name);
        foreach ($this->headers as $key => $value) {
            if (strtolower($key) === $needle) {
                return $value;
            }
        }
        return null;
    }

    /** @return array<string,mixed> */
    public function json(): array
    {
        if ($this->body === '') {
            return [];
        }
        $decoded = json_decode($this->body, true);
        if (!is_array($decoded) || json_last_error() !== JSON_ERROR_NONE) {
            throw new ApiException('Platform returned invalid JSON.', $this->statusCode, $this->header('X-Request-ID'));
        }
        return $decoded;
    }
}
