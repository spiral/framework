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
        Entity::LOCAL_KEY  => '{entity:primaryKey}',
        Entity::OUTER_KEY  => '{entity:roleName}_{definition:LOCAL_KEY}',
        Entity::CONSTRAINT => true
    );

    /**
     * Create all required relation columns, indexes and constraints.
     */
    public function buildSchema()
    {
        $outerSchema = $this->outerEntity()->getTableSchema();

        $outerKey = $outerSchema->column($this->definition[Entity::OUTER_KEY]);
        $outerKey->type($this->entitySchema->getPrimaryAbstractType());
        $outerKey->nullable();
        $outerKey->index();

        if ($this->definition[Entity::CONSTRAINT])
        {
            $outerKey->foreign(
                $this->entitySchema->getTable(),
                $this->definition[Entity::LOCAL_KEY]
            )->onDelete('CASCADE')->onUpdate('CASCADE');
        }
    }
}