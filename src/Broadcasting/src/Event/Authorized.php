<?php

declare(strict_types=1);

namespace Spiral\Broadcasting\Event;

use Psr\Http\Message\ServerRequestInterface;
use Spiral\Broadcasting\AuthorizationStatus;

final class Authorized
{
    public function __construct(
        public AuthorizationStatus $status,
        public readonly ServerRequestInterface $request
    ) {
    }
}
