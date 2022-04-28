<?php

declare(strict_types=1);

namespace Spiral\Filters\Attribute;

use Attribute;
use Spiral\Attributes\NamedArgumentConstructor;

#[Attribute(Attribute::TARGET_PROPERTY), NamedArgumentConstructor]
final class Setter
{
    public function __construct(
        public readonly string|array $filter
    ) {
    }

    public function updateValue(mixed $value): mixed
    {
        return \call_user_func($this->filter, $value);
    }
}
