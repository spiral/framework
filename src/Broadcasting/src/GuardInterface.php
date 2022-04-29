<?php

declare(strict_types=1);

namespace Spiral\Broadcasting;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface GuardInterface
{
    /**
     * Authenticate a websocket connection request.
     */
    public function authorize(
        ServerRequestInterface $request
    ): AuthorizationStatus;
}
