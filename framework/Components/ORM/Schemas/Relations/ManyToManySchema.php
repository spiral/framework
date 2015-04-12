<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ORM\Schemas\Relations;

use Spiral\Components\ORM\Entity;
use Spiral\Components\ORM\ORMException;
use Spiral\Components\ORM\Schemas\RelationSchema;

class ManyToManySchema extends RelationSchema
{
    /**
     * Relation type.
     */
    const RELATION_TYPE = Entity::MANY_TO_MANY;

    /**
     * Equivalent relationship resolved based on definition and not schema, usually polymorphic.
     */
    const EQUIVALENT_RELATION = Entity::MANY_TO_MORPHED;

    /**
     * Default definition parameters, will be filled if parameter skipped from definition by user.
     *
     * @invisible
     * @var array
     */
    protected $defaultDefinition = array(
        Entity::INNER_KEY         => '{entity:primaryKey}',
        Entity::OUTER_KEY         => '{outer:primaryKey}',
        Entity::THOUGHT_INNER_KEY => '{entity:roleName}_{definition:INNER_KEY}',
        Entity::THOUGHT_OUTER_KEY => '{outer:roleName}_{definition:OUTER_KEY}',
        Entity::CONSTRAINT        => true,
        Entity::CONSTRAINT_ACTION => 'CASCADE',
        Entity::CREATE_PIVOT      => false
    );

    /**
     * Mount default values to relation definition.
     */
    protected function clarifyDefinition()
    {
        parent::clarifyDefinition();

        if (empty($this->definition[Entity::PIVOT_TABLE]))
        {
            $this->definition[Entity::PIVOT_TABLE] = $this->getPivotTableName();
        }
    }

    /**
     * Pivot table name.
     *
     * @return string
     */
    public function getPivotTableName()
    {
        if (isset($this->definition[Entity::PIVOT_TABLE]))
        {
            return $this->definition[Entity::PIVOT_TABLE];
        }

        //Generating pivot table name
        $names = array(
            $this->entitySchema->getRoleName(),
            $this->outerEntity()->getRoleName()
        );

        asort($names);

        return join('_', $names) . '_map';
    }

    /**
     * Create all required relation columns, indexes and constraints.
     */
    public function buildSchema()
    {
        if (!$this->definition[Entity::CREATE_PIVOT])
        {
            return;
        }

        $pivotTable = $this->ormSchema->declareTable(
            $this->entitySchema->getDatabase(),
            $this->getPivotTableName()
        );

        $pivotTable->bigPrimary('id');

        $innerKey = $pivotTable->column($this->definition[Entity::THOUGHT_INNER_KEY]);
        $innerKey->type($this->getInnerKeyType());

        $outerKey = $pivotTable->column($this->definition[Entity::THOUGHT_OUTER_KEY]);
        $outerKey->type($this->getOuterKeyType());

        //Complex index
        $pivotTable->unique(
            $this->definition[Entity::THOUGHT_INNER_KEY],
            $this->definition[Entity::THOUGHT_OUTER_KEY]
        );

        if ($this->definition[Entity::CONSTRAINT] && empty($this->definition[Entity::MORPH_KEY]))
        {
            $foreignKey = $innerKey->foreign(
                $this->entitySchema->getTable(),
                $this->entitySchema->getPrimaryKey()
            );
            $foreignKey->onDelete($this->definition[Entity::CONSTRAINT_ACTION]);
            $foreignKey->onUpdate($this->definition[Entity::CONSTRAINT_ACTION]);

            $foreignKey = $outerKey->foreign(
                $this->outerEntity()->getTable(),
                $this->outerEntity()->getPrimaryKey()
            );
            $foreignKey->onDelete($this->definition[Entity::CONSTRAINT_ACTION]);
            $foreignKey->onUpdate($this->definition[Entity::CONSTRAINT_ACTION]);
        }
    }

    /**
     * Create reverted relations in outer entity or entities.
     *
     * @param string $name Relation name.
     * @param int    $type Back relation type, can be required some cases.
     * @throws ORMException
     */
    public function revertRelation($name, $type = null)
    {
        $this->outerEntity()->addRelation($name, array(
            Entity::MANY_TO_MANY      => $this->entitySchema->getClass(),
            Entity::PIVOT_TABLE       => $this->definition[Entity::PIVOT_TABLE],
            Entity::OUTER_KEY         => $this->definition[Entity::INNER_KEY],
            Entity::INNER_KEY         => $this->definition[Entity::OUTER_KEY],
            Entity::THOUGHT_INNER_KEY => $this->definition[Entity::THOUGHT_OUTER_KEY],
            Entity::THOUGHT_OUTER_KEY => $this->definition[Entity::THOUGHT_INNER_KEY],
            Entity::CONSTRAINT        => $this->definition[Entity::CONSTRAINT],
            Entity::CONSTRAINT_ACTION => $this->definition[Entity::CONSTRAINT_ACTION],
            Entity::CREATE_PIVOT      => $this->definition[Entity::CREATE_PIVOT]
        ));
    }
}