<?php

declare(strict_types=1);

namespace Spiral\Filters\Model\Mapper;

use Spiral\Filters\Exception\SetterException;
use Spiral\Filters\Model\FilterInterface;

final class DefaultCaster implements CasterInterface
{
    public function supports(\ReflectionNamedType $type): bool
    {
        return true;
    }

    public function setValue(FilterInterface $filter, \ReflectionProperty $property, mixed $value): void
    {
        try {
            $property->setValue($filter, $value);
        } catch (\Throwable $e) {
            throw new SetterException(
                previous: $e,
                message: \sprintf('Unable to set value. %s', $e->getMessage()),
            );
        }
    }
}
