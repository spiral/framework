<?php

declare(strict_types=1);

namespace Spiral\Broadcasting\Event;

use Psr\Http\Message\ServerRequestInterface;

final class AuthorizationSuccess
{
    public function __construct(
        public readonly ServerRequestInterface $request
    ) {
    }
}
