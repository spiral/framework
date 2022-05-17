<?php

declare(strict_types=1);

namespace Spiral\Filters\Attribute;

use Attribute;
use Spiral\Attributes\NamedArgumentConstructor;

/**
 * Use setters to typecast the incoming value before passing it to the property.
 *
 * Example 1:
 * #[\Spiral\Filters\Attribute\Setter(filter: 'trim')]
 *
 * Example 2:
 * #[\Spiral\Filters\Attribute\Setter(filter: [Foo::class, 'bar'])]
 */
#[Attribute(Attribute::TARGET_PROPERTY), NamedArgumentConstructor]
final class Setter
{
    public readonly \Closure $filter;

    public function __construct(callable $filter)
    {
        $this->filter = $filter(...);
    }

    public function updateValue(mixed $value): mixed
    {
        return ($this->filter)($value);
    }
}
