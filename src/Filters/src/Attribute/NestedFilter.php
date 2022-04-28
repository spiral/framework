<?php

declare(strict_types=1);

namespace Spiral\Filters\Attribute;

use Attribute;
use Spiral\Attributes\NamedArgumentConstructor;

#[Attribute(Attribute::TARGET_PROPERTY), NamedArgumentConstructor]
class NestedFilter
{
    /**
     * @param class-string $class
     */
    public function __construct(
        public readonly string $class,
        public readonly ?string $prefix = null
    ) {
    }

    public function getSchema(\ReflectionProperty $property): string|array
    {
        if ($this->prefix) {
            return [$this->class, $this->prefix];
        }

        return $this->class;
    }
}
