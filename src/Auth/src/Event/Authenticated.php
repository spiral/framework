<?php

declare(strict_types=1);

namespace Spiral\Auth\Event;

use Spiral\Auth\TokenInterface;

final class Authenticated
{
    public function __construct(
        public readonly TokenInterface $token,
        public readonly ?string $transport = null
    ) {
    }
}
