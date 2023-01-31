<?php

declare(strict_types=1);

namespace Spiral\Core\Attribute;

/**
 * Define a finalize method for the class.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class Finalize
{
    public function __construct(
        public string $method,
    ) {
    }
}
