<?php

declare(strict_types=1);

namespace Spiral\Http;

use Psr\Http\Message\ResponseInterface;

interface EmitterInterface
{
    /**
     * Emit response to the user.
     */
    public function emit(ResponseInterface $response): bool;
}
