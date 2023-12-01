<?php

declare(strict_types=1);

namespace Spiral\Filters\Model\Mapper;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Spiral\Filters\Exception\SetterException;
use Spiral\Filters\Model\FilterInterface;

final class UuidCaster implements CasterInterface
{
    private ?bool $interfaceExists = null;

    public function supports(\ReflectionNamedType $type): bool
    {
        if ($this->interfaceExists === null) {
            $this->interfaceExists = \interface_exists(UuidInterface::class);
        }

        return $this->interfaceExists &&
            !$type->isBuiltin() &&
            $this->implements($type->getName(), UuidInterface::class);
    }

    public function setValue(FilterInterface $filter, \ReflectionProperty $property, mixed $value): void
    {
        try {
            $property->setValue($filter, $value instanceof UuidInterface ? $value : Uuid::fromString($value));
        } catch (\Throwable $e) {
            throw new SetterException(
                previous: $e,
                message: \sprintf('Unable to set UUID value. %s', $e->getMessage()),
            );
        }
    }

    private function implements(string $haystack, string $interface): bool
    {
        if ($haystack === $interface) {
            return true;
        }

        if (!\class_exists($haystack)) {
            return false;
        }

        foreach ((array)\class_implements($haystack) as $implements) {
            if ($implements === $interface) {
                return true;
            }

            if (self::implements($implements, $interface)) {
                return true;
            }
        }

        return false;
    }
}
