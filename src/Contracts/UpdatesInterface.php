<?php

declare(strict_types=1);

namespace Foundwell\Contracts;

interface UpdatesInterface
{
    public function latest(string $channel = 'stable'): array;
}
