<?php

declare(strict_types=1);

namespace Spiral\Http\Event;

use Psr\Http\Message\ServerRequestInterface;

final class RequestReceived
{
    public function __construct(
        public readonly ServerRequestInterface $request
    ) {
    }
}
