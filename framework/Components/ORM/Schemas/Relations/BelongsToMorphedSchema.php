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
use Spiral\Components\ORM\ORMException;
use Spiral\Components\ORM\Schemas\MorphedRelationSchema;

class BelongsToMorphedSchema extends MorphedRelationSchema
{
    /**
     * Relation type.
     */
    const RELATION_TYPE = Entity::BELONGS_TO_MORPHED;

    /**
     * Default definition parameters, will be filled if parameter skipped from definition by user.
     *
     * @invisible
     * @var array
     */
    protected $defaultDefinition = array(
        Entity::INNER_KEY => '{name:singular}_{outer:primaryKey}',
        Entity::MORPH_KEY => '{name:singular}_type'
    );

    /**
     * Create all required relation columns, indexes and constraints.
     *
     * @throws ORMException
     */
    public function buildSchema()
    {
        if (empty($this->targets))
        {
            //No targets found, no need to generate anything
            return;
        }

        $innerSchema = $this->entitySchema->getTableSchema();

        $morphKey = $innerSchema->column($this->definition[Entity::MORPH_KEY]);
        $morphKey->string(static::TYPE_COLUMN_SIZE);

        $innerKey = $innerSchema->column($this->definition[Entity::INNER_KEY]);
        $innerKey->type($this->outerPrimaryAbstractType);

        $innerSchema->index(
            $this->definition[Entity::MORPH_KEY],
            $this->definition[Entity::INNER_KEY]
        );
    }
}