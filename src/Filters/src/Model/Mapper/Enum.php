<?php

declare(strict_types=1);

namespace Spiral\Filters\Model\Mapper;

use Spiral\Filters\Model\FilterInterface;

final class Enum implements SetterInterface
{
    public function supports(\ReflectionNamedType $type): bool
    {
        return \enum_exists($type->getName());
    }

    public function setValue(FilterInterface $filter, \ReflectionProperty $property, mixed $value): void
    {
        /**
         * @var \ReflectionNamedType $type
         */
        $type = $property->getType();

        /**
         * @var class-string<\BackedEnum> $enum
         */
        $enum = $type->getName();

        $property->setValue($filter, $enum::from($value));
    }
}
