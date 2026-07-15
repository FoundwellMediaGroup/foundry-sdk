<?php

declare(strict_types=1);

namespace Foundwell\Contracts;

use Foundwell\Http\Request;
use Foundwell\Http\Response;

interface TransportInterface
{
    public function send(Request $request): Response;
}
