<?php

declare(strict_types=1);

namespace Spiral\Tests\Http\Telemetry;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Returns the given response and keeps the request it was sent.
 */
final class RecordingClient implements ClientInterface
{
    public ?RequestInterface $sent = null;

    public function __construct(
        private readonly ResponseInterface $response,
    ) {}

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->sent = $request;

        return $this->response;
    }
}
