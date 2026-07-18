<?php

declare(strict_types=1);

namespace Foundwell\Authentication;

use Foundwell\Config;

final class HeaderAuthenticator
{
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /** @param array<string,string> $headers
     *  @return array<string,string>
     */
    public function apply(array $headers): array
    {
        $headers['Accept'] = 'application/json';
        $headers['User-Agent'] = sprintf(
            'Foundwell-SDK-PHP/%s %s/%s',
            ClientVersion::VERSION,
            $this->config->product(),
            $this->config->version()
        );
        $headers['X-Foundwell-Product'] = $this->config->product();
        $headers['X-Foundwell-Product-Version'] = $this->config->version();

        if ($this->config->apiKey() !== null && $this->config->apiKey() !== '') {
            $headers['Authorization'] = 'Bearer ' . $this->config->apiKey();
        }

        return $headers;
    }
}

final class ClientVersion
{
    public const VERSION = '0.3.1-alpha';
    private function __construct() {}
}
