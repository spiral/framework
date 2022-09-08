<?php

declare(strict_types=1);

namespace Spiral\Http\Event;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;

final class MiddlewareProcessing
{
    public function __construct(
        public readonly ServerRequestInterface $request,
        public readonly MiddlewareInterface $middleware
    ) {
    }
}
