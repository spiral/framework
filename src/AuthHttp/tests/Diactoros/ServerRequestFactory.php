<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Auth\Diactoros;

use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Laminas\Diactoros\ServerRequest;

final class ServerRequestFactory implements ServerRequestFactoryInterface
{
    /**
     * @param string              $method
     * @param UriInterface|string $uri
     * @param array               $serverParams
     * @return ServerRequestInterface
     */
    public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
    {
        return new ServerRequest($serverParams, [], $uri, $method);
    }
}
