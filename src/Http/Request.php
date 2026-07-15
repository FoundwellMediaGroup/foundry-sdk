<?php

declare(strict_types=1);

namespace Foundwell\Http;

use Foundwell\Exceptions\ValidationException;

final class Request
{
    private string $method;
    private string $url;
    /** @var array<string,string> */
    private array $headers;
    private ?string $body;
    private int $connectTimeout;
    private int $requestTimeout;

    /** @param array<string,string> $headers */
    public function __construct(
        string $method,
        string $url,
        array $headers = [],
        ?string $body = null,
        int $connectTimeout = 5,
        int $requestTimeout = 15
    ) {
        $method = strtoupper(trim($method));
        if (!in_array($method, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            throw new ValidationException('Unsupported HTTP method: ' . $method);
        }
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new ValidationException('A valid request URL is required.');
        }

        $this->method = $method;
        $this->url = $url;
        $this->headers = $headers;
        $this->body = $body;
        $this->connectTimeout = $connectTimeout;
        $this->requestTimeout = $requestTimeout;
    }

    public function method(): string { return $this->method; }
    public function url(): string { return $this->url; }
    /** @return array<string,string> */
    public function headers(): array { return $this->headers; }
    public function body(): ?string { return $this->body; }
    public function connectTimeout(): int { return $this->connectTimeout; }
    public function requestTimeout(): int { return $this->requestTimeout; }
}
