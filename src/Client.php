<?php

declare(strict_types=1);

namespace Foundwell;

use Foundwell\Contracts\LoggerInterface;
use Foundwell\Contracts\TransportInterface;
use Foundwell\Http\CurlTransport;
use Foundwell\Http\HttpClient;
use Foundwell\Http\RetryPolicy;
use Foundwell\Licensing\InstallationIdentity;
use Foundwell\Licensing\LicensingService;
use Foundwell\Logging\NullLogger;

final class Client
{
    private Config $config;
    private HttpClient $http;
    private LicensingService $licensing;

    public function __construct(
        Config $config,
        ?TransportInterface $transport = null,
        ?LoggerInterface $logger = null,
        ?InstallationIdentity $identity = null
    ) {
        $this->config = $config;
        $this->http = new HttpClient(
            $config,
            $transport ?? new CurlTransport(),
            $logger ?? new NullLogger(),
            new RetryPolicy($config->maxAttempts(), $config->retryDelayMilliseconds())
        );
        $this->licensing = new LicensingService($config, $this->http, $identity);
    }

    public function config(): Config { return $this->config; }
    public function http(): HttpClient { return $this->http; }
    public function licenses(): LicensingService { return $this->licensing; }
    public function sdkVersion(): string { return '0.3.0-alpha'; }
}
