<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ORM\Schemas\Relations;

use Spiral\Components\DBAL\Schemas\AbstractTableSchema;
use Spiral\Components\ORM\ActiveRecord;
use Spiral\Components\ORM\ORMException;
use Spiral\Components\ORM\Schemas\RelationSchema;

class ManyToManySchema extends RelationSchema
{
    /**
     * Relation type.
     */
    const RELATION_TYPE = ActiveRecord::MANY_TO_MANY;

    /**
     * Equivalent relationship resolved based on definition and not schema, usually polymorphic.
     */
    const EQUIVALENT_RELATION = ActiveRecord::MANY_TO_MORPHED;

    /**
     * Default definition parameters, will be filled if parameter skipped from definition by user.
     *
     * @invisible
     * @var array
     */
    protected $defaultDefinition = [
        ActiveRecord::INNER_KEY         => '{record:primaryKey}',
        ActiveRecord::OUTER_KEY         => '{outer:primaryKey}',
        ActiveRecord::THOUGHT_INNER_KEY => '{record:roleName}_{definition:INNER_KEY}',
        ActiveRecord::THOUGHT_OUTER_KEY => '{outer:roleName}_{definition:OUTER_KEY}',
        ActiveRecord::CONSTRAINT        => true,
        ActiveRecord::CONSTRAINT_ACTION => 'CASCADE',
        ActiveRecord::CREATE_PIVOT  => true,
        ActiveRecord::PIVOT_COLUMNS => [],
        ActiveRecord::WHERE_PIVOT   => [],
        ActiveRecord::WHERE         => []
    ];

    /**
     * Inverse relation.
     *
     * @throws ORMException
     */
    public function inverseRelation()
    {
        $this->getOuterModel()->addRelation(
            $this->definition[ActiveRecord::INVERSE],
            [
                ActiveRecord::MANY_TO_MANY      => $this->model->getClass(),
                ActiveRecord::PIVOT_TABLE       => $this->definition[ActiveRecord::PIVOT_TABLE],
                ActiveRecord::OUTER_KEY         => $this->definition[ActiveRecord::INNER_KEY],
                ActiveRecord::INNER_KEY         => $this->definition[ActiveRecord::OUTER_KEY],
                ActiveRecord::THOUGHT_INNER_KEY => $this->definition[ActiveRecord::THOUGHT_OUTER_KEY],
                ActiveRecord::THOUGHT_OUTER_KEY => $this->definition[ActiveRecord::THOUGHT_INNER_KEY],
                ActiveRecord::CONSTRAINT        => $this->definition[ActiveRecord::CONSTRAINT],
                ActiveRecord::CONSTRAINT_ACTION => $this->definition[ActiveRecord::CONSTRAINT_ACTION],
                ActiveRecord::CREATE_PIVOT      => $this->definition[ActiveRecord::CREATE_PIVOT],
                ActiveRecord::PIVOT_COLUMNS     => $this->definition[ActiveRecord::PIVOT_COLUMNS]
            ]
        );
    }

    /**
     * Mount default values to relation definition.
     */
    protected function clarifyDefinition()
    {
        parent::clarifyDefinition();
        if (empty($this->definition[ActiveRecord::PIVOT_TABLE]))
        {
            $this->definition[ActiveRecord::PIVOT_TABLE] = $this->getPivotTable();
        }

        if ($this->isOuterDatabase())
        {
            throw new ORMException("Many-to-Many relation can not point to outer database data.");
        }
    }

    /**
     * Pivot table name.
     *
     * @return string
     */
    public function getPivotTable()
    {
        if (isset($this->definition[ActiveRecord::PIVOT_TABLE]))
        {
            return $this->definition[ActiveRecord::PIVOT_TABLE];
        }

        //Generating pivot table name
        $names = [$this->model->getRoleName(), $this->getOuterModel()->getRoleName()];
        asort($names);

        return join('_', $names) . '_map';
    }

    /**
     * Pivot table schema.
     *
     * @return AbstractTableSchema
     */
    public function getPivotSchema()
    {
        return $this->builder->table($this->model->getDatabase(), $this->getPivotTable());
    }

    /**
     * Create all required relation columns, indexes and constraints.
     */
    public function buildSchema()
    {
        if (!$this->definition[ActiveRecord::CREATE_PIVOT])
        {
            //We are working purely with pivot table in this relation
            return;
        }

        $pivotTable = $this->getPivotSchema();

        $outerKey = $pivotTable->column($this->definition[ActiveRecord::THOUGHT_OUTER_KEY]);
        $outerKey->type($this->getOuterKeyType());

        if (!empty($this->definition[ActiveRecord::MORPH_KEY]))
        {
            $morphKey = $pivotTable->column($this->definition[ActiveRecord::MORPH_KEY]);
            $morphKey->string(static::MORPH_COLUMN_SIZE);
        }

        $innerKey = $pivotTable->column($this->definition[ActiveRecord::THOUGHT_INNER_KEY]);
        $innerKey->type($this->getInnerKeyType());

        //Additional pivot columns
        foreach ($this->definition[ActiveRecord::PIVOT_COLUMNS] as $column => $definition)
        {
            $this->castColumn($pivotTable->column($column), $definition);
        }

        if (!$this->isConstrained() || !empty($this->definition[ActiveRecord::MORPH_KEY]))
        {
            //Either not need to create constraint or it was created in polymorphic relation
            return;
        }

        //Complex index
        $pivotTable->unique(
            $this->definition[ActiveRecord::THOUGHT_INNER_KEY],
            $this->definition[ActiveRecord::THOUGHT_OUTER_KEY]
        );

        $foreignKey = $innerKey->foreign(
            $this->model->getTable(),
            $this->model->getPrimaryKey()
        );

        $foreignKey->onDelete($this->getConstraintAction());
        $foreignKey->onUpdate($this->getConstraintAction());

        $foreignKey = $outerKey->foreign(
            $this->getOuterModel()->getTable(),
            $this->getOuterModel()->getPrimaryKey()
        );

        $foreignKey->onDelete($this->getConstraintAction());
        $foreignKey->onUpdate($this->getConstraintAction());
    }

    /**
     * Normalize relation options.
     *
     * @return array
     */
    protected function normalizeDefinition()
    {
        $definition = parent::normalizeDefinition();

        //Let's include pivot table columns
        $definition[ActiveRecord::PIVOT_COLUMNS] = [];
        foreach ($this->getPivotSchema()->getColumns() as $column)
        {
            $definition[ActiveRecord::PIVOT_COLUMNS][] = $column->getName();
        }

        return $definition;
    }
}