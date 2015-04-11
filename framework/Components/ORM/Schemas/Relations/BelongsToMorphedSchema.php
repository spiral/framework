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

    /**
     * Create reverted relations in outer entity or entities.
     *
     * @param string $name Relation name.
     * @param int    $type Back relation type, can be required some cases.
     * @throws ORMException
     */
    public function revertRelation($name, $type = null)
    {
        if (empty($type))
        {
            throw new ORMException(
                "Unable to revert BELONG_TO relation ({$this->entitySchema}), " .
                "back relation type is missing."
            );
        }

        foreach ($this->getTargets() as $entity)
        {
            $entity->addRelation($name, array(
                $type             => $this->entitySchema->getClass(),
                Entity::OUTER_KEY => $this->definition[Entity::INNER_KEY],
                Entity::MORPH_KEY => $this->definition[Entity::MORPH_KEY]
            ));
        }
    }
}