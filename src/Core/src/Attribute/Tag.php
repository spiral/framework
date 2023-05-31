<?php

declare(strict_types=1);

namespace Spiral\Core\Attribute;

/**
 * @internal We are testing this feature, it may be changed in the future.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class Tag implements Plugin
{
    public function __construct(
        public string $name,
    ) {
    }
}
