<?php

declare(strict_types=1);

namespace Foundwell\Contracts;

interface TelemetryInterface
{
    public function send(array $events): array;
}
