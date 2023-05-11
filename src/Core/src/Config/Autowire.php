<?php

declare(strict_types=1);

namespace Spiral\Core\Config;

use Spiral\Core\Container\Autowire as AutowireAlias;

/**
 * Wraps {@see AutowireAlias}.
 */
final class Autowire extends Binding
{
    public function __construct(
        public readonly AutowireAlias $autowire,
        public readonly bool $singleton = false,
    ) {
    }

    public function __toString(): string
    {
        return 'Autowire object';
    }
}
