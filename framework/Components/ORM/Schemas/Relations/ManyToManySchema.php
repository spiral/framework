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

class ManyToManySchema extends RelationSchema
{
    /**
     * Relation type.
     */
    const RELATION_TYPE = Entity::MANY_TO_MANY;

    /**
     * Equivalent relationship resolved based on definition and not schema, usually polymorphic.
     */
    const EQUIVALENT_RELATION = Entity::MANY_TO_MANY_MORPHED;

    public function initiate()
    {
        $target = $this->getTargetEntity();

        $mapTable = empty($this->definition[Entity::PIVOT_TABLE])
            ? $this->entitySchema->getTable() . '_' . $target->getTable() . '_map'
            : $this->definition[Entity::PIVOT_TABLE];
        $table = $this->ormSchema->getTableSchema($this->entitySchema->getDatabase(), $mapTable);

        $table->bigPrimary('id');

        //column type and name
        $table->column($this->entitySchema->getRoleName() . '_id')->integer()->foreign($this->entitySchema->getTable(), 'id');
        $table->column($target->getRoleName() . '_id')->integer()->foreign($target->getTable(), 'id');

        $table->index($this->entitySchema->getRoleName() . '_id', $target->getRoleName() . '_id')->unique(true);
    }
}