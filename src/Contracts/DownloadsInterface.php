<?php

declare(strict_types=1);

namespace Foundwell\Contracts;

interface DownloadsInterface
{
    public function release(string $version): array;
}
