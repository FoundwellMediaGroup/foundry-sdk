<?php

declare(strict_types=1);

namespace Foundwell\Tests\Fixtures;

use Foundwell\Contracts\TransportInterface;
use Foundwell\Http\Request;
use Foundwell\Http\Response;

final class FakeTransport implements TransportInterface
{
    /** @var Response[] */
    private array $responses;
    /** @var Request[] */
    private array $requests = [];

    /** @param Response[] $responses */
    public function __construct(array $responses)
    {
        $this->responses = array_values($responses);
    }

    public function send(Request $request): Response
    {
        $this->requests[] = $request;
        if ($this->responses === []) {
            throw new \RuntimeException('Fake transport has no response queued.');
        }
        return array_shift($this->responses);
    }

    /** @return Request[] */
    public function requests(): array { return $this->requests; }
}
