<?php

declare(strict_types=1);

namespace Spiral\Core\Attribute;

/**
 * Set a scope in which the dependency can be resolved.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class Scope
{
    public function __construct(
        public string $name,
    ) {
    }
}
