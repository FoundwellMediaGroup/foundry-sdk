<?php

declare(strict_types=1);

namespace Foundwell;

use Foundwell\Exceptions\ConfigurationException;

final class Config
{
    private string $baseUrl;
    private string $product;
    private string $version;
    private ?string $apiKey;
    private int $connectTimeout;
    private int $requestTimeout;

    public function __construct(
        string $baseUrl,
        string $product,
        string $version,
        ?string $apiKey = null,
        int $connectTimeout = 5,
        int $requestTimeout = 15
    ) {
        $baseUrl = rtrim(trim($baseUrl), '/');
        $product = trim($product);
        $version = trim($version);

        if ($baseUrl === '' || filter_var($baseUrl, FILTER_VALIDATE_URL) === false) {
            throw new ConfigurationException('A valid Foundwell Platform base URL is required.');
        }

        if ($product === '') {
            throw new ConfigurationException('A product identifier is required.');
        }

        if ($version === '') {
            throw new ConfigurationException('A product version is required.');
        }

        if ($connectTimeout < 1 || $requestTimeout < 1) {
            throw new ConfigurationException('Timeout values must be greater than zero.');
        }

        $this->baseUrl = $baseUrl;
        $this->product = $product;
        $this->version = $version;
        $this->apiKey = $apiKey;
        $this->connectTimeout = $connectTimeout;
        $this->requestTimeout = $requestTimeout;
    }

    public function baseUrl(): string { return $this->baseUrl; }
    public function product(): string { return $this->product; }
    public function version(): string { return $this->version; }
    public function apiKey(): ?string { return $this->apiKey; }
    public function connectTimeout(): int { return $this->connectTimeout; }
    public function requestTimeout(): int { return $this->requestTimeout; }
}
