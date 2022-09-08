<?php

declare(strict_types=1);

namespace Spiral\Router\Event;

use Psr\Http\Message\ServerRequestInterface;
use Spiral\Router\RouteInterface;

final class RouteMatched
{
    public function __construct(
        public readonly ServerRequestInterface $request,
        public readonly RouteInterface $route,
    ) {
    }
}
