<?php

declare(strict_types=1);

namespace Spiral\Boot\Event;

use Spiral\Boot\FinalizerInterface;

/**
 * The Event will be fired when finalizer are executed before running finalizers.
 */
final class Finalizing
{
    public function __construct(
        public readonly FinalizerInterface $finalizer
    ) {
    }
}
