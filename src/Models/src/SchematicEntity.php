<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Models;

/**
 * Entity which code follows external behaviour schema.
 */
class SchematicEntity extends AbstractEntity
{
    /** @var array */
    private $schema = [];

    public function __construct(array $data, array $schema)
    {
        $this->schema = $schema;
        parent::__construct($data);
    }

    /**
     * {@inheritdoc}
     */
    protected function isFillable(string $field): bool
    {
        if (!empty($this->schema[ModelSchema::FILLABLE]) && $this->schema[ModelSchema::FILLABLE] === '*') {
            return true;
        }

        if (!empty($this->schema[ModelSchema::FILLABLE])) {
            return in_array($field, $this->schema[ModelSchema::FILLABLE], true);
        }

        if (!empty($this->schema[ModelSchema::SECURED]) && $this->schema[ModelSchema::SECURED] === '*') {
            return false;
        }

        return !in_array($field, $this->schema[ModelSchema::SECURED], true);
    }

    /**
     * {@inheritdoc}
     */
    protected function getMutator(string $field, string $type)
    {
        if (isset($this->schema[ModelSchema::MUTATORS][$type][$field])) {
            return $this->schema[ModelSchema::MUTATORS][$type][$field];
        }

        return null;
    }
}
