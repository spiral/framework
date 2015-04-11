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

class HasOneSchema extends RelationSchema
{
    /**
     * Relation type.
     */
    const RELATION_TYPE = Entity::HAS_ONE;

    /**
     * Default definition parameters, will be filled if parameter skipped from definition by user.
     *
     * @invisible
     * @var array
     */
    protected $defaultDefinition = array(
        Entity::OUTER_KEY         => '{entity:roleName}_{definition:INNER_KEY}',
        Entity::CONSTRAINT        => true,
        Entity::CONSTRAINT_ACTION => 'CASCADE'
    );

    /**
     * Create all required relation columns, indexes and constraints.
     */
    public function buildSchema()
    {
        $outerSchema = $this->outerEntity()->getTableSchema();

        $outerKey = $outerSchema->column($this->definition[Entity::OUTER_KEY]);
        $outerKey->type($this->entitySchema->getPrimaryAbstractType());
        $outerKey->nullable(true);
        $outerKey->index();

        if ($this->definition[Entity::CONSTRAINT] && empty($this->definition[Entity::MORPH_KEY]))
        {
            $foreignKey = $outerKey->foreign(
                $this->entitySchema->getTable(),
                $this->entitySchema->getPrimaryKey()
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
            Entity::BELONGS_TO        => $this->entitySchema->getClass(),
            Entity::INNER_KEY         => $this->definition[Entity::OUTER_KEY],
            Entity::CONSTRAINT        => $this->definition[Entity::CONSTRAINT],
            Entity::CONSTRAINT_ACTION => $this->definition[Entity::CONSTRAINT_ACTION]
        ));
    }
}