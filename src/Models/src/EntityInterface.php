<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Models;

use Spiral\Models\Exception\EntityExceptionInterface;

/**
 * Generic data entity instance.
 */
interface EntityInterface extends \ArrayAccess
{
    /**
     * Check if field known to entity, field value can be null!
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasField(string $name): bool;

    /**
     * Set entity field value.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @throws EntityExceptionInterface
     */
    public function setField(string $name, $value);

    /**
     * Get value of entity field.
     *
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     *
     * @throws EntityExceptionInterface
     */
    public function getField(string $name, $default = null);

    /**
     * Update entity fields using mass assignment. Only allowed fields must be set.
     *
     * @param array|\Traversable $fields
     *
     * @throws EntityExceptionInterface
     */
    public function setFields($fields = []);

    /**
     * Get entity field values.
     *
     * @return array
     *
     * @throws EntityExceptionInterface
     */
    public function getFields(): array;
}
