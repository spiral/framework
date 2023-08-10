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
        $type = $property->getType();
        if ($type === null || !$type instanceof \ReflectionNamedType) {
            return;
        }

        /**
         * @var class-string<\BackedEnum> $enum
         */
        $enum = $type->getName();

        $property->setValue($filter, $enum::from($value));
    }
}
