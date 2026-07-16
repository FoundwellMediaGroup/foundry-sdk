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
    private int $maxAttempts;
    private int $retryDelayMilliseconds;
    private ?string $licenseCachePath;

    public function __construct(
        string $baseUrl,
        string $product,
        string $version,
        ?string $apiKey = null,
        int $connectTimeout = 5,
        int $requestTimeout = 15,
        int $maxAttempts = 3,
        int $retryDelayMilliseconds = 200,
        ?string $licenseCachePath = null
    ) {
        $baseUrl = rtrim(trim($baseUrl), '/');
        $product = trim($product);
        $version = trim($version);

        if ($baseUrl === '' || filter_var($baseUrl, FILTER_VALIDATE_URL) === false) {
            throw new ConfigurationException('A valid Foundwell Platform base URL is required.');
        }
        if (strtolower((string) parse_url($baseUrl, PHP_URL_SCHEME)) !== 'https') {
            throw new ConfigurationException('Foundwell Platform connections must use HTTPS.');
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
        if ($maxAttempts < 1 || $maxAttempts > 5) {
            throw new ConfigurationException('Maximum attempts must be between 1 and 5.');
        }
        if ($retryDelayMilliseconds < 0 || $retryDelayMilliseconds > 10000) {
            throw new ConfigurationException('Retry delay must be between 0 and 10000 milliseconds.');
        }

        $this->baseUrl = $baseUrl;
        $this->product = $product;
        $this->version = $version;
        $this->apiKey = $apiKey !== null ? trim($apiKey) : null;
        $this->connectTimeout = $connectTimeout;
        $this->requestTimeout = $requestTimeout;
        $this->maxAttempts = $maxAttempts;
        $this->retryDelayMilliseconds = $retryDelayMilliseconds;
        $licenseCachePath = $licenseCachePath !== null ? trim($licenseCachePath) : null;
        $this->licenseCachePath = $licenseCachePath !== '' ? $licenseCachePath : null;
    }

    public function baseUrl(): string { return $this->baseUrl; }
    public function product(): string { return $this->product; }
    public function version(): string { return $this->version; }
    public function apiKey(): ?string { return $this->apiKey; }
    public function connectTimeout(): int { return $this->connectTimeout; }
    public function requestTimeout(): int { return $this->requestTimeout; }
    public function maxAttempts(): int { return $this->maxAttempts; }
    public function retryDelayMilliseconds(): int { return $this->retryDelayMilliseconds; }
    public function licenseCachePath(): ?string { return $this->licenseCachePath; }
}
