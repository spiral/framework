<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ORM\Schemas\Relationships;

use Doctrine\Common\Inflector\Inflector;
use Spiral\Components\ORM\Entity;
use Spiral\Components\ORM\Schemas\EntitySchema;
use Spiral\Components\ORM\Schemas\RelationshipSchema;

class ManyToManyMorphedSchema extends RelationshipSchema
{
    /**
     * Relationship type.
     */
    const RELATIONSHIP_TYPE = Entity::MANY_TO_MANY_MORPHED;

    public function cast(EntitySchema $schema)
    {
        $mapTable = $this->definition[Entity::PIVOT_TABLE];

        $table = $this->ormSchema->getTableSchema($schema->getDatabase(), $mapTable);

        $table->bigPrimary('id');

        //integer vs bigInteger
        $table->column($schema->getRoleName() . '_id')
            ->integer()
            ->index();

        $table->column($schema->getRoleName() . '_id')->foreign($schema->getTable(), 'id');

        $table->column($this->name . '_type')->string(32);

        //TODO: check primary key type
        $table->column($this->name . '_id')->integer();

        $table->index($this->name . '_type', $this->name . '_id');
    }
}