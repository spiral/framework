<?php

declare(strict_types=1);

namespace Spiral\Boot\Event;

use Spiral\Boot\FinalizerInterface;

final class Finalizing
{
    public function __construct(
        public readonly FinalizerInterface $finalizer
    ) {
    }
}
