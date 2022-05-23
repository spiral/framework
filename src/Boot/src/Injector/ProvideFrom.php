<?php

declare(strict_types=1);

namespace Spiral\Boot\Injector;

use Spiral\Attributes\NamedArgumentConstructor;

#[\Attribute(\Attribute::TARGET_CLASS), NamedArgumentConstructor]
final class ProvideFrom
{
    public function __construct(
        public readonly string $method
    ) {
    }
}
