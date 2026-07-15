<?php

declare(strict_types=1);

namespace Foundwell;

use Foundwell\Contracts\LoggerInterface;
use Foundwell\Contracts\TransportInterface;
use Foundwell\Http\CurlTransport;
use Foundwell\Http\HttpClient;
use Foundwell\Http\RetryPolicy;
use Foundwell\Logging\NullLogger;

final class Client
{
    private Config $config;
    private HttpClient $http;

    public function __construct(
        Config $config,
        ?TransportInterface $transport = null,
        ?LoggerInterface $logger = null
    ) {
        $this->config = $config;
        $this->http = new HttpClient(
            $config,
            $transport ?? new CurlTransport(),
            $logger ?? new NullLogger(),
            new RetryPolicy($config->maxAttempts(), $config->retryDelayMilliseconds())
        );
    }

    public function config(): Config { return $this->config; }
    public function http(): HttpClient { return $this->http; }
    public function sdkVersion(): string { return '0.2.0-alpha'; }
}
