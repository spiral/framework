<?php

declare(strict_types=1);

namespace Spiral\Http\Diactoros;

use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Laminas\Diactoros\ServerRequest;

final class ServerRequestFactory implements ServerRequestFactoryInterface
{
    /**
     * @param UriInterface|string $uri
     */
    public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
    {
        return new ServerRequest($serverParams, [], $uri, $method);
    }
}
