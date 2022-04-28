<?php

declare(strict_types=1);

namespace Spiral\Filters\Attribute\Input;

use Spiral\Attributes\NamedArgumentConstructor;
use Spiral\Filters\InputInterface;

#[\Attribute(\Attribute::TARGET_PROPERTY), NamedArgumentConstructor]
final class Route extends Input
{
    /**
     * @param non-empty-string|null $key
     */
    public function __construct(
        public readonly ?string $key = null,
    ) {
    }

    public function getValue(InputInterface $input, \ReflectionProperty $property): mixed
    {
        return $input->getValue('attribute', 'matches.' . ($this->key ?? $property->getName()));
    }

    public function getSchema(\ReflectionProperty $property): string
    {
        return 'attribute:matches.' . ($this->key ?? $property->getName());
    }
}
