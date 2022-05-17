<?php

declare(strict_types=1);

namespace Spiral\Filters\Attribute\Input;

use Spiral\Filters\InputInterface;

abstract class AbstractInput
{
    abstract public function getValue(InputInterface $input, \ReflectionProperty $property): mixed;

    abstract public function getSchema(\ReflectionProperty $property): string;

    protected function getKey(\ReflectionProperty $property): string
    {
        return $this->key ?? $property->getName();
    }
}
