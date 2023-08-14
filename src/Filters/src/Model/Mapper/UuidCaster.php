<?php

declare(strict_types=1);

namespace Spiral\Filters\Model\Mapper;

use Ramsey\Uuid\UuidInterface;
use Spiral\Filters\Model\FilterInterface;

final class UuidCaster implements CasterInterface
{
    private ?bool $interfaceExists = null;

    public function supports(\ReflectionNamedType $type): bool
    {
        if ($this->interfaceExists === null) {
            $this->interfaceExists = \interface_exists(UuidInterface::class);
        }

        return $this->interfaceExists && $this->implements($type->getName(), UuidInterface::class);
    }

    public function setValue(FilterInterface $filter, \ReflectionProperty $property, mixed $value): void
    {
        $property->setValue(
            $filter,
            $value instanceof UuidInterface ? $value : \Ramsey\Uuid\Uuid::fromString($value)
        );
    }

    private function implements(string $haystack, string $interface): bool
    {
        if ($haystack === $interface) {
            return true;
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
