<?php

declare(strict_types=1);

namespace Spiral\Console\Attribute;

use Spiral\Attributes\NamedArgumentConstructor;

#[\Attribute(\Attribute::TARGET_PROPERTY), NamedArgumentConstructor]
final class Argument
{
    /**
     * @param ?non-empty-string $name Argument name. Property name by default
     * @param ?non-empty-string $description Argument description
     * @param \Closure|array $suggestedValues Argument suggested values
     */
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $description = null,
        public readonly \Closure|array $suggestedValues = [],
    ) {
    }
}
