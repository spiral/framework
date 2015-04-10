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
    const RELATIONSHIP_TYPE = Entity::HAS_ONE;

    public function initiate()
    {
        $this->define(Entity::LOCAL_KEY, '{entity:pK}');
        $this->define(Entity::FOREIGN_KEY, '{entity:roleName}_{rel:LOCAL_KEY}');

        $foreignSchema = $this->getTargetEntity()->getTableSchema();

        //Generate names
        $foreignSchema->column($this->entitySchema->getRoleName() . '_id')->integer()
            ->foreign($this->entitySchema->getTable(), 'id')
            ->onDelete('CASCADE')
            ->onUpdate('CASCADE');

        $foreignSchema->column($this->entitySchema->getRoleName() . '_id')->index();
    }
}