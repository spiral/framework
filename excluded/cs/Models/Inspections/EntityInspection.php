<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Models\Inspections;

use Spiral\Models\Inspector;
use Spiral\Models\Reflections\ReflectionEntity;

/**
 * Performs fields analysis for DataEntity fields.
 */
class EntityInspection
{
    /**
     * @var FieldInspection[]
     */
    private $fields = [];

    /**
     * @var ReflectionEntity
     */
    private $reflection = null;

    /**
     * @var Inspector
     */
    protected $inspector = null;

    /**
     * @param Inspector        $inspector
     * @param ReflectionEntity $entity
     */
    public function __construct(Inspector $inspector, ReflectionEntity $entity)
    {
        $this->inspector = $inspector;
        $this->reflection = $entity;

        $this->inspectFields();
    }

    /**
     * Entity name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->reflection->getName();
    }

    /**
     * @return ReflectionEntity
     */
    public function getReflection()
    {
        return $this->reflection;
    }

    /**
     * Get model fields inspections.
     *
     * @return FieldInspection[]
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Count fields.
     *
     * @return int
     */
    public function countFields()
    {
        return count($this->fields);
    }

    /**
     * Count fillable fields.
     *
     * @return int
     */
    public function countFillable()
    {
        $fillable = 0;

        foreach ($this->fields as $field) {
            if ($field->isFillable()) {
                $fillable++;
            }
        }

        return $fillable;
    }

    /**
     * Count validated fields.
     *
     * @return int
     */
    public function countValidated()
    {
        $fillable = 0;

        foreach ($this->fields as $field) {
            if ($field->isValidated()) {
                $fillable++;
            }
        }

        return $fillable;
    }

    /**
     * Average field rank. Ranking value measures between 0 and 1.
     *
     * @return float
     */
    public function getRank()
    {
        if (empty($this->fields)) {
            return 1;
        }

        $rank = 0;
        foreach ($this->fields as $field) {
            $rank += $field->getRank();
        }

        return $rank / $this->countFields();
    }

    /**
     * Inspect model fields and generate FieldInspections.
     */
    protected function inspectFields()
    {
        foreach ($this->reflection->getFields() as $field => $type) {
            $this->fields[$field] = $this->inspectField($field);
        }
    }

    /**
     * Create inspection for model field.
     *
     * @param string $field
     * @return FieldInspection
     */
    private function inspectField($field)
    {
        $filters = $this->reflection->getSetters() + $this->reflection->getAccessors();
        $fillable = true;

        if (
            $this->reflection->getSecured() === '*'
            || in_array($field, $this->reflection->getSecured())
        ) {
            $fillable = false;
        }

        if ($this->reflection->getFillable() != []) {
            $fillable = in_array($field, $this->reflection->getFillable());
        }

        return new FieldInspection(
            $this->inspector,
            $field,
            $this->reflection->getFields()[$field],
            $fillable,
            in_array($field, $this->reflection->getHidden()),
            isset($filters[$field]),
            array_key_exists($field, $this->reflection->getValidates())
        );
    }
}