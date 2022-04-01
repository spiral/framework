<?php

declare(strict_types=1);

namespace Spiral\Models;

use Spiral\Models\Exception\EntityException;

/**
 * DataEntity in spiral used to represent basic data set with filters and accessors. Most of spiral
 * models (ORM and ODM, HttpFilters) will extend data entity.
 */
class DataEntity extends AbstractEntity
{
    /**
     * Set of fields allowed to be filled using setFields() method.
     *
     * @see setFields()
     * @var array
     */
    protected const FILLABLE = [];

    /**
     * List of fields not allowed to be filled by setFields() method. Replace with and empty array
     * to allow all fields.
     *
     * By default all entity fields are settable! Opposite behaviour has to be described in entity
     * child implementations.
     *
     * @see setFields()
     * @var array|string
     */
    protected const SECURED = '*';

    /**
     * @see setField()
     * @var array
     */
    protected const SETTERS = [];

    /**
     * @see getField()
     * @var array
     */
    protected const GETTERS = [];

    /**
     * Accessor used to mock field data and filter every request thought itself.
     *
     * @see getField()
     * @see setField()
     * @var array
     */
    protected const ACCESSORS = [];

    /**
     * Check if field can be set using setFields() method.
     *
     * @see  $secured
     * @see  setField()
     * @see  $fillable
     */
    protected function isFillable(string $field): bool
    {
        return match (true) {
            static::FILLABLE === '*' => true,
            !empty(static::FILLABLE) => \in_array($field, static::FILLABLE, true),
            static::SECURED === '*' => false,
            default => !\in_array($field, static::SECURED, true)
        };
    }

    /**
     * Check and return name of mutator (getter, setter, accessor) associated with specific field.
     *
     * @param string $type Mutator type (setter, getter, accessor).
     *
     * @throws EntityException
     */
    protected function getMutator(string $field, string $type): mixed
    {
        $target = match ($type) {
            ModelSchema::MUTATOR_ACCESSOR => static::ACCESSORS,
            ModelSchema::MUTATOR_GETTER => static::GETTERS,
            ModelSchema::MUTATOR_SETTER => static::SETTERS
        };

        if (isset($target[$field])) {
            return $target[$field];
        }

        return null;
    }
}
