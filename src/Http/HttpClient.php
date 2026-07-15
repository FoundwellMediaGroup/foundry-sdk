<?php

declare(strict_types=1);

namespace Foundwell\Http;

use Foundwell\Authentication\HeaderAuthenticator;
use Foundwell\Config;
use Foundwell\Contracts\LoggerInterface;
use Foundwell\Contracts\TransportInterface;
use Foundwell\Exceptions\ApiException;
use Foundwell\Exceptions\AuthenticationException;
use Foundwell\Exceptions\AuthorizationException;
use Foundwell\Exceptions\ConnectionException;
use Foundwell\Exceptions\TimeoutException;

final class HttpClient
{
    private Config $config;
    private TransportInterface $transport;
    private LoggerInterface $logger;
    private RetryPolicy $retryPolicy;
    private HeaderAuthenticator $authenticator;

    public function __construct(
        Config $config,
        TransportInterface $transport,
        LoggerInterface $logger,
        RetryPolicy $retryPolicy
    ) {
        $this->config = $config;
        $this->transport = $transport;
        $this->logger = $logger;
        $this->retryPolicy = $retryPolicy;
        $this->authenticator = new HeaderAuthenticator($config);
    }

    /** @param array<string,mixed>|null $json
     *  @param array<string,string> $headers
     */
    public function request(string $method, string $path, ?array $json = null, array $headers = []): Response
    {
        $url = $this->config->baseUrl() . '/' . ltrim($path, '/');
        $body = null;
        if ($json !== null) {
            $body = json_encode($json, JSON_UNESCAPED_SLASHES);
            if ($body === false) {
                throw new ApiException('Unable to encode request JSON.');
            }
            $headers['Content-Type'] = 'application/json';
        }
        $headers = $this->authenticator->apply($headers);

        $lastException = null;
        for ($attempt = 1; $attempt <= $this->retryPolicy->maxAttempts(); $attempt++) {
            $request = new Request(
                $method,
                $url,
                $headers,
                $body,
                $this->config->connectTimeout(),
                $this->config->requestTimeout()
            );

            try {
                $this->logger->debug('Sending Foundwell Platform request.', [
                    'method' => $method,
                    'url' => $url,
                    'attempt' => $attempt,
                ]);
                $response = $this->transport->send($request);
            } catch (TimeoutException | ConnectionException $exception) {
                $lastException = $exception;
                if ($attempt >= $this->retryPolicy->maxAttempts()) {
                    throw $exception;
                }
                $this->logger->warning('Foundwell Platform request failed; retrying.', [
                    'attempt' => $attempt,
                    'error' => $exception->getMessage(),
                ]);
                $this->sleep($attempt);
                continue;
            }

            if ($this->retryPolicy->shouldRetryStatus($response->statusCode()) && $attempt < $this->retryPolicy->maxAttempts()) {
                $this->logger->warning('Foundwell Platform returned retryable response.', [
                    'attempt' => $attempt,
                    'status' => $response->statusCode(),
                    'request_id' => $response->header('X-Request-ID'),
                ]);
                $this->sleep($attempt);
                continue;
            }

            $this->throwForError($response);
            return $response;
        }

        throw $lastException ?? new ConnectionException('Foundwell Platform request failed.');
    }

    private function sleep(int $attempt): void
    {
        $milliseconds = $this->retryPolicy->delayMilliseconds($attempt);
        if ($milliseconds > 0) {
            usleep($milliseconds * 1000);
        }
    }

    private function throwForError(Response $response): void
    {
        if ($response->isSuccessful()) {
            return;
        }

        $message = 'Foundwell Platform returned HTTP ' . $response->statusCode() . '.';
        try {
            $payload = $response->json();
            if (isset($payload['message']) && is_string($payload['message'])) {
                $message = $payload['message'];
            } elseif (isset($payload['error']) && is_string($payload['error'])) {
                $message = $payload['error'];
            }
        } catch (ApiException $ignored) {
            // Preserve the HTTP error when the body is not JSON.
        }

        $requestId = $response->header('X-Request-ID');
        if ($response->statusCode() === 401) {
            throw new AuthenticationException($message, 401, $requestId);
        }
        if ($response->statusCode() === 403) {
            throw new AuthorizationException($message, 403, $requestId);
        }
        throw new ApiException($message, $response->statusCode(), $requestId);
    }
}
