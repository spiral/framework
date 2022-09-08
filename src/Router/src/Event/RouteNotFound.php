<?php

declare(strict_types=1);

namespace Spiral\Router\Event;

use Psr\Http\Message\ServerRequestInterface;

final class RouteNotFound
{
    public function __construct(
        public readonly ServerRequestInterface $request
    ) {
    }
}
