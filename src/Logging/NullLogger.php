<?php

declare(strict_types=1);

namespace Foundwell\Logging;

use Foundwell\Contracts\LoggerInterface;

final class NullLogger implements LoggerInterface
{
    public function debug(string $message, array $context = []): void {}
    public function warning(string $message, array $context = []): void {}
    public function error(string $message, array $context = []): void {}
}
