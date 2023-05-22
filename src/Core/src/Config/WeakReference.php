<?php

declare(strict_types=1);

namespace Spiral\Core\Config;

/**
 * A value will be returned as weak reference. It will be recreated automatically if the value is destroyed.
 */
final class WeakReference extends Binding
{
    public function __construct(
        public \WeakReference $reference,
    ) {
    }

    public function __toString(): string
    {
        return 'Weak reference';
    }
}
