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
use Spiral\Components\ORM\Schemas\RelationSchema;

class ManyToManyMorphedSchema extends RelationSchema
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
        Entity::LOCAL_KEY         => '{name:singular}_{outer:primaryKey}',
        Entity::LOCAL_TYPE        => '{name:singular}_type',
        Entity::CONSTRAINT        => true,
        Entity::CONSTRAINT_ACTION => 'CASCADE'
    );

    /**
     * Create all required relation columns, indexes and constraints.
     */
    public function buildSchema()
    {
        $pivotTable = $this->ormSchema->declareTable(
            $this->entitySchema->getDatabase(),
            $this->definition[Entity::PIVOT_TABLE]
        );

        $pivotTable->bigPrimary('id');

        dumP($this->definition);
        echo 1;


        //        $localKey = $pivotTable->column($this->definition[Entity::LOCAL_KEY]);
        //        $localKey->type($this->entitySchema->getPrimaryAbstractType());
        //
        //        $outerKey = $pivotTable->column($this->definition[Entity::OUTER_KEY]);
        //        $outerKey->type($this->outerEntity()->getPrimaryAbstractType());
        //
        //        //Complex index
        //        $pivotTable->unique(
        //            $this->definition[Entity::LOCAL_KEY],
        //            $this->definition[Entity::OUTER_KEY]
        //        );
        //
        //        if ($this->definition[Entity::CONSTRAINT])
        //        {
        //            $foreignKey = $localKey->foreign(
        //                $this->entitySchema->getTable(),
        //                $this->entitySchema->getPrimaryKey()
        //            );
        //            $foreignKey->onDelete($this->definition[Entity::CONSTRAINT_ACTION]);
        //            $foreignKey->onUpdate($this->definition[Entity::CONSTRAINT_ACTION]);
        //
        //            $foreignKey = $outerKey->foreign(
        //                $this->outerEntity()->getTable(),
        //                $this->outerEntity()->getPrimaryKey()
        //            );
        //            $foreignKey->onDelete($this->definition[Entity::CONSTRAINT_ACTION]);
        //            $foreignKey->onUpdate($this->definition[Entity::CONSTRAINT_ACTION]);
        //        }
    }
}