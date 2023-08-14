<?php

declare(strict_types=1);

namespace Spiral\Filters\Attribute;

use Attribute;
use Spiral\Attributes\NamedArgumentConstructor;
use Spiral\Filters\Exception\SetterException;

/**
 * Use setters to typecast the incoming value before passing it to the property.
 *
 * Example 1:
 * #[\Spiral\Filters\Attribute\Setter(filter: 'trim')]
 *
 * Example 2:
 * #[\Spiral\Filters\Attribute\Setter(filter: [Foo::class, 'bar'])]
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE), NamedArgumentConstructor]
class Setter
{
    public readonly \Closure $filter;
    protected array $args;

    public function __construct(callable $filter, mixed ...$args)
    {
        $this->filter = $filter(...);
        $this->args = $args;
    }

    public function updateValue(mixed $value): mixed
    {
        try {
            return ($this->filter)($value, ...$this->args);
        } catch (\Throwable $e) {
            throw new SetterException($e);
        }
    }
}
