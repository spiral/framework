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

class BelongsToMorphedSchema extends RelationSchema
{
    /**
     * Relation type.
     */
    const RELATION_TYPE = Entity::BELONGS_TO_MORPHED;

    public function buildSchema()
    {
        $table = $this->entitySchema->getTableSchema();
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