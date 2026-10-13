<?php

declare(strict_types=1);

namespace Spiral\Tests\Http\Diactoros;

use Nyholm\Psr7\ServerRequest;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

final class ServerRequestFactory implements ServerRequestFactoryInterface
{
    /**
     * @param UriInterface|string $uri
     */
    public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
    {
        return new ServerRequest($method, $uri, serverParams: $serverParams);
    }
}
