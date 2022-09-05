<?php

declare(strict_types=1);

namespace Spiral\Router\Exception;

use Psr\Http\Message\UriInterface;

class RouteNotFoundException extends UndefinedRouteException
{
    public function __construct(
        private readonly UriInterface $uri,
        int $code = 0,
        \Throwable $previous = null
    ) {
        parent::__construct(\sprintf('Unable to route `%s`.', (string) $uri), $code, $previous);
    }

    public function getUri(): UriInterface
    {
        return $this->uri;
    }
}
