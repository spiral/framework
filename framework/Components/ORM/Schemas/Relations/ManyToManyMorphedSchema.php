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
use Spiral\Components\ORM\Schemas\MorphedRelationSchema;

class ManyToManyMorphedSchema extends MorphedRelationSchema
{
    /**
     * Relation type.
     */
    const RELATION_TYPE = Entity::MANY_TO_MANY_MORPHED;

    /**
     * Default definition parameters, will be filled if parameter skipped from definition by user.
     *
     * @invisible
     * @var array
     */
    protected $defaultDefinition = array(
        Entity::PIVOT_TABLE       => '{name:singular}_map',
        Entity::LOCAL_KEY         => '{entity:roleName}_{entity:primaryKey}',
        Entity::OUTER_KEY         => '{name:singular}_{outer:primaryKey}',
        Entity::OUTER_TYPE        => '{name:singular}_type',
        Entity::CONSTRAINT        => true,
        Entity::CONSTRAINT_ACTION => 'CASCADE'
    );

    /**
     * Create all required relation columns, indexes and constraints.
     */
    public function buildSchema()
    {
        if (empty($this->targets))
        {
            //No targets found, no need to generate anything
            return;
        }

        $pivotTable = $this->ormSchema->declareTable(
            $this->entitySchema->getDatabase(),
            $this->definition[Entity::PIVOT_TABLE]
        );

        $pivotTable->bigPrimary('id');

        $localKey = $pivotTable->column($this->definition[Entity::LOCAL_KEY]);
        $localKey->type($this->entitySchema->getPrimaryAbstractType());
        $localKey->index();

        $outerKey = $pivotTable->column($this->definition[Entity::OUTER_KEY]);
        $outerKey->type($this->outerPrimaryAbstractType);

        //Building outer keys
        $outerType = $pivotTable->column($this->definition[Entity::OUTER_TYPE]);
        $outerType->string(static::TYPE_COLUMN_SIZE);

        //Complex index
        $pivotTable->unique(
            $this->definition[Entity::LOCAL_KEY],
            $this->definition[Entity::OUTER_KEY],
            $this->definition[Entity::OUTER_TYPE]
        );

        if ($this->definition[Entity::CONSTRAINT])
        {
            $foreignKey = $localKey->foreign(
                $this->entitySchema->getTable(),
                $this->entitySchema->getPrimaryKey()
            );
            $foreignKey->onDelete($this->definition[Entity::CONSTRAINT_ACTION]);
            $foreignKey->onUpdate($this->definition[Entity::CONSTRAINT_ACTION]);
        }
    }
}