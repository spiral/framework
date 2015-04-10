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
use Spiral\Components\ORM\Schemas\RelationshipSchema;

class BelongsToMorphedSchema extends RelationshipSchema
{
    /**
     * Relationship type.
     */
    const RELATIONSHIP_TYPE = Entity::BELONGS_TO_MORPHED;

    public function cast(EntitySchema $schema)
    {
        $table = $schema->getTableSchema();

        dump($table->getColumns());

        $table->column($this->name . '_type')->string(32);

        //TODO: check primary key type
        $table->column($this->name . '_id')->integer();

        $table->index($this->name . '_type', $this->name . '_id');

        //Generate names
        //        $schema->column($schema->getRoleName() . '_id')->integer()
        //            ->foreign($schema->getTable(), 'id')
        //            ->onDelete('CASCADE')
        //            ->onUpdate('CASCADE');

        //   $schema->column($schema->getRoleName() . '_id')->index();
    }
}