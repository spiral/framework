<?php

declare(strict_types=1);

namespace Spiral\Router\Event;

use Spiral\Router\RouteInterface;

final class RouteFound
{
    public function __construct(
        public readonly RouteInterface $route
    ) {
    }
}
