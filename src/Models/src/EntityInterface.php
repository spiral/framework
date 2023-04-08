<?php

declare(strict_types=1);

namespace Spiral\Models;

use Spiral\Models\Exception\EntityExceptionInterface;

/**
 * Generic data entity instance.
 *
 * @extends \ArrayAccess<string, mixed>
 */
interface EntityInterface extends \ArrayAccess
{
    /**
     * Check if field known to entity, field value can be null!
     */
    public function hasField(string $name): bool;

    /**
     * Set entity field value.
     *
     * @throws EntityExceptionInterface
     */
    public function setField(string $name, mixed $value): self;

    /**
     * Get value of entity field.
     *
     * @throws EntityExceptionInterface
     */
    public function getField(string $name, mixed $default = null): mixed;

    /**
     * Update entity fields using mass assignment. Only allowed fields must be set.
     *
     * @throws EntityExceptionInterface
     */
    public function setFields(iterable $fields = []): self;

    /**
     * Get entity field values.
     *
     * @throws EntityExceptionInterface
     */
    public function getFields(): array;
}
