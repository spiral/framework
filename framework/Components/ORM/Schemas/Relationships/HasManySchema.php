<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ORM\Schemas\Relationships;

use Spiral\Components\ORM\Entity;
use Spiral\Components\ORM\Schemas\EntitySchema;

class HasManySchema extends HasOneSchema
{
    /**
     * Relationship type.
     */
    const RELATIONSHIP_TYPE = Entity::HAS_MANY;

    public function cast(EntitySchema $schema)
    {
        $foreignSchema = $this->getTargetEntity()->getTableSchema();

        //Generate names
        $foreignSchema->column($schema->getRoleName() . '_id')->integer()
            ->foreign($schema->getTable(), 'id')
            ->onDelete('CASCADE')
            ->onUpdate('CASCADE');

        $foreignSchema->column($schema->getRoleName() . '_id')->index();
    }
}