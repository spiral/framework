<?php

declare(strict_types=1);

namespace Spiral\Filters\Model\Mapper;

use Spiral\Filters\Model\FilterInterface;

interface CasterInterface
{
    public function supports(\ReflectionNamedType $type): bool;

    public function setValue(FilterInterface $filter, \ReflectionProperty $property, mixed $value): void;
}
