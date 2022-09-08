<?php

declare(strict_types=1);

namespace Spiral\Auth\Event;

final class Logout
{
    public function __construct(
        public readonly ?object $actor,
        public readonly ?string $transport = null
    ) {
    }
}
