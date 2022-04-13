<?php

declare(strict_types=1);

namespace Spiral\Broadcasting;

use Psr\Http\Message\ServerRequestInterface;

interface GuardInterface
{
    public function authorize(ServerRequestInterface $request): bool;
}
