<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Models;

use ArrayAccess;
use Spiral\Models\Exception\EntityExceptionInterface;

/**
 * Generic data entity instance.
 */
interface EntityInterface extends ArrayAccess
{
    /**
     * Check if field known to entity, field value can be null!
     *
     *
     */
    public function hasField(string $name): bool;

    /**
     * Set entity field value.
     *
     * @param mixed  $value
     * @throws EntityExceptionInterface
     */
    public function setField(string $name, $value);

    /**
     * Get value of entity field.
     *
     * @param mixed  $default
     *
     * @return mixed
     * @throws EntityExceptionInterface
     */
    public function getField(string $name, $default = null);

    /**
     * Update entity fields using mass assignment. Only allowed fields must be set.
     *
     * @throws EntityExceptionInterface
     */
    public function setFields(iterable $fields = []);

    /**
     * Get entity field values.
     *
     *
     * @throws EntityExceptionInterface
     */
    public function getFields(): array;
}
