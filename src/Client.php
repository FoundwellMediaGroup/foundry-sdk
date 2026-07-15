<?php

declare(strict_types=1);

namespace Foundwell;

final class Client
{
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function config(): Config
    {
        return $this->config;
    }

    public function sdkVersion(): string
    {
        return '0.1.0-alpha';
    }
}
