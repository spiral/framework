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

class ManyToManySchema extends RelationshipSchema
{
    /**
     * Relationship type.
     */
    const RELATIONSHIP_TYPE = Entity::MANY_TO_MANY;

    /**
     * Equivalent relationship resolved based on definition and not schema, usually polymorphic.
     */
    const EQUIVALENT_RELATIONSHIP = Entity::MANY_TO_MANY_MORPHED;

    public function cast(EntitySchema $schema)
    {
        $target = $this->getTargetEntity();

        $mapTable = empty($this->definition[Entity::PIVOT_TABLE])
            ? $schema->getTable() . '_' . $target->getTable() . '_map'
            : $this->definition[Entity::PIVOT_TABLE];
        $table = $this->ormSchema->getTableSchema($schema->getDatabase(), $mapTable);

        $table->bigPrimary('id');

        //column type and name
        $table->column($schema->getRoleName() . '_id')->integer()->foreign($schema->getTable(), 'id');
        $table->column($target->getRoleName() . '_id')->integer()->foreign($target->getTable(), 'id');

        $table->index($schema->getRoleName() . '_id', $target->getRoleName() . '_id')->unique(true);
    }
}