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
use Spiral\Components\ORM\ORM;
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
        ActiveRecord::PIVOT_COLUMNS => []
    ];

    /**
     * Mount default values to relation definition.
     */
    protected function clarifyDefinition()
    {
        parent::clarifyDefinition();

        if (empty($this->definition[ActiveRecord::PIVOT_TABLE]))
        {
            $this->definition[ActiveRecord::PIVOT_TABLE] = $this->getPivotTableName();
        }
    }

    /**
     * Pivot table name.
     *
     * @return string
     */
    public function getPivotTableName()
    {
        if (isset($this->definition[ActiveRecord::PIVOT_TABLE]))
        {
            return $this->definition[ActiveRecord::PIVOT_TABLE];
        }

        //Generating pivot table name
        $names = [
            $this->recordSchema->getRoleName(),
            $this->getOuterRecordSchema()->getRoleName()
        ];

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
        return $this->schemaBuilder->declareTable(
            $this->recordSchema->getDatabase(),
            $this->getPivotTableName()
        );
    }

    /**
     * Create all required relation columns, indexes and constraints.
     */
    public function buildSchema()
    {
        if (!$this->definition[ActiveRecord::CREATE_PIVOT])
        {
            return;
        }

        $pivotTable = $this->getPivotSchema();

        $outerKey = $pivotTable->column($this->definition[ActiveRecord::THOUGHT_OUTER_KEY]);
        $outerKey->type($this->getOuterKeyType());

        if (!empty($this->definition[ActiveRecord::MORPH_KEY]))
        {
            $morphKey = $pivotTable->column($this->definition[ActiveRecord::MORPH_KEY]);
            $morphKey->string(static::TYPE_COLUMN_SIZE);
        }

        $innerKey = $pivotTable->column($this->definition[ActiveRecord::THOUGHT_INNER_KEY]);
        $innerKey->type($this->getInnerKeyType());

        if ($this->definition[ActiveRecord::CONSTRAINT] && empty($this->definition[ActiveRecord::MORPH_KEY]))
        {
            //Complex index
            $pivotTable->unique(
                $this->definition[ActiveRecord::THOUGHT_INNER_KEY],
                $this->definition[ActiveRecord::THOUGHT_OUTER_KEY]
            );

            $foreignKey = $innerKey->foreign(
                $this->recordSchema->getTable(),
                $this->recordSchema->getPrimaryKey()
            );

            $foreignKey->onDelete($this->definition[ActiveRecord::CONSTRAINT_ACTION]);
            $foreignKey->onUpdate($this->definition[ActiveRecord::CONSTRAINT_ACTION]);

            $foreignKey = $outerKey->foreign(
                $this->getOuterRecordSchema()->getTable(),
                $this->getOuterRecordSchema()->getPrimaryKey()
            );

            $foreignKey->onDelete($this->definition[ActiveRecord::CONSTRAINT_ACTION]);
            $foreignKey->onUpdate($this->definition[ActiveRecord::CONSTRAINT_ACTION]);
        }
    }

    /**
     * Create reverted relations in outer model or models.
     *
     * @param string $name Relation name.
     * @param int    $type Back relation type, can be required some cases.
     * @throws ORMException
     */
    public function revertRelation($name, $type = null)
    {
        $this->getOuterRecordSchema()->addRelation($name, [
            ActiveRecord::MANY_TO_MANY      => $this->recordSchema->getClass(),
            ActiveRecord::PIVOT_TABLE       => $this->definition[ActiveRecord::PIVOT_TABLE],
            ActiveRecord::OUTER_KEY         => $this->definition[ActiveRecord::INNER_KEY],
            ActiveRecord::INNER_KEY         => $this->definition[ActiveRecord::OUTER_KEY],
            ActiveRecord::THOUGHT_INNER_KEY => $this->definition[ActiveRecord::THOUGHT_OUTER_KEY],
            ActiveRecord::THOUGHT_OUTER_KEY => $this->definition[ActiveRecord::THOUGHT_INNER_KEY],
            ActiveRecord::CONSTRAINT        => $this->definition[ActiveRecord::CONSTRAINT],
            ActiveRecord::CONSTRAINT_ACTION => $this->definition[ActiveRecord::CONSTRAINT_ACTION],
            ActiveRecord::CREATE_PIVOT      => $this->definition[ActiveRecord::CREATE_PIVOT]
        ]);
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