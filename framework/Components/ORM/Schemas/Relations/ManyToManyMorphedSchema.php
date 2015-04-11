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

    public function initiate()
    {
        $mapTable = $this->definition[Entity::PIVOT_TABLE];

        $table = $this->ormSchema->getTableSchema($this->entitySchema->getDatabase(), $mapTable);

        $table->bigPrimary('id');

        //integer vs bigInteger
        $table->column($this->entitySchema->getRoleName() . '_id')
            ->integer()
            ->index();

        $table->column($this->entitySchema->getRoleName() . '_id')->foreign($this->entitySchema->getTable(), 'id');

        $table->column($this->name . '_type')->string(32);

        //TODO: check primary key type
        $table->column($this->name . '_id')->integer();

        $table->index($this->name . '_type', $this->name . '_id');
    }
}