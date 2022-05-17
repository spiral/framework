<?php

declare(strict_types=1);

namespace Spiral\Models;

/**
 * Entity which code follows external behaviour schema.
 */
class SchematicEntity extends AbstractEntity
{
    public function __construct(
        array $data,
        private array $schema
    ) {
        parent::__construct($data);
    }

    protected function isFillable(string $field): bool
    {
        return match (true) {
            !empty($this->schema[ModelSchema::FILLABLE]) && $this->schema[ModelSchema::FILLABLE] === '*' => true,
            !empty($this->schema[ModelSchema::FILLABLE]) => \in_array($field, $this->schema[ModelSchema::FILLABLE], true),
            !empty($this->schema[ModelSchema::SECURED]) && $this->schema[ModelSchema::SECURED] === '*' => false,
            default => !\in_array($field, $this->schema[ModelSchema::SECURED], true)
        };
    }

    protected function getMutator(string $field, string $type): mixed
    {
        if (isset($this->schema[ModelSchema::MUTATORS][$type][$field])) {
            return $this->schema[ModelSchema::MUTATORS][$type][$field];
        }

        return null;
    }
}
