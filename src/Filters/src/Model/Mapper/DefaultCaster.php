<?php

declare(strict_types=1);

namespace Spiral\Filters\Model\Mapper;

use Spiral\Filters\Model\FilterInterface;

final class DefaultCaster implements CasterInterface
{
    public function supports(\ReflectionNamedType $type): bool
    {
        return true;
    }

    public function setValue(FilterInterface $filter, \ReflectionProperty $property, mixed $value): void
    {
        $property->setValue($filter, $value);
    }
}
