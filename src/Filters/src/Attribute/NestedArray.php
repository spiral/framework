<?php

declare(strict_types=1);

namespace Spiral\Filters\Attribute;

use Attribute;
use Spiral\Attributes\NamedArgumentConstructor;
use Spiral\Filters\Attribute\Input\Input;
use Spiral\Filters\InputInterface;

#[Attribute(Attribute::TARGET_PROPERTY), NamedArgumentConstructor]
final class NestedArray
{
    /**
     * @param class-string $class
     * @param non-empty-string|null $prefix
     */
    public function __construct(
        public readonly string $class,
        public readonly Input $input,
        public readonly ?string $prefix = null
    ) {
    }

    public function getValue(InputInterface $input, \ReflectionProperty $property): mixed
    {
        return $this->input->getValue($input, $property);
    }

    public function getSchema(\ReflectionProperty $property): array
    {
        if ($this->prefix) {
            return [$this->class, $this->prefix];
        }

        return [$this->class];
    }
}
