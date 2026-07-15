<?php

declare(strict_types=1);

namespace Foundwell\Contracts;

interface HealthInterface
{
    public function report(array $metrics): array;
}
