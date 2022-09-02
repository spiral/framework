<?php

declare(strict_types=1);

namespace Spiral\Http\Event;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class RequestHandled
{
    public function __construct(
        public readonly ServerRequestInterface $request,
        public readonly ResponseInterface $response
    ) {
    }
}
