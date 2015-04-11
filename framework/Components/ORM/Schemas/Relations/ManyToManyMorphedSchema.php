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

    /**
     * Default definition parameters, will be filled if parameter skipped from definition by user.
     *
     * @invisible
     * @var array
     */
    protected $defaultDefinition = array(
        Entity::PIVOT_TABLE => '{name}',
        Entity::LOCAL_KEY   => '{name:singular}_{foreign:primaryKey}',
        Entity::LOCAL_TYPE  => '{name:singular}_type'
    );

    const TYPE_COLUMN_SIZE = 32;

    public function buildSchema()
    {
        $table = $this->ormSchema->declareTable(
            $this->entitySchema->getDatabase(),
            $this->definition[Entity::PIVOT_TABLE]
        );

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