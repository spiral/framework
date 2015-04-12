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
use Spiral\Components\ORM\Schemas\RelationSchema;

class ManyThoughtSchema extends RelationSchema
{
    /**
     * Relation type.
     */
    const RELATION_TYPE = Entity::MANY_THOUGHT;

    /**
     * Default definition parameters, will be filled if parameter skipped from definition by user.
     *
     * @invisible
     * @var array
     */
    protected $defaultDefinition = array(
        Entity::INNER_KEY         => '{entity:primaryKey}',
        Entity::OUTER_KEY         => '{outer:primaryKey}',
        Entity::THOUGHT_INNER_KEY => '{entity:roleName}_{definition:INNER_KEY}',
        Entity::THOUGHT_OUTER_KEY => '{outer:roleName}_{definition:OUTER_KEY}'
    );

    /**
     * Create all required relation columns, indexes and constraints.
     *
     * @throws ORMException
     */
    public function buildSchema()
    {
        if (empty($this->definition[Entity::PIVOT_TABLE]))
        {
            throw new ORMException(
                "Unable to build MANY_THOUGHT ({$this->entitySchema}) relation, "
                . "thought table has to be specified."
            );
        }
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
        $this->outerEntity()->addRelation($name, array(
            Entity::MANY_THOUGHT      => $this->entitySchema->getClass(),
            Entity::OUTER_KEY         => $this->definition[Entity::INNER_KEY],
            Entity::INNER_KEY         => $this->definition[Entity::OUTER_KEY],
            Entity::THOUGHT_INNER_KEY => $this->definition[Entity::THOUGHT_OUTER_KEY],
            Entity::THOUGHT_OUTER_KEY => $this->definition[Entity::THOUGHT_INNER_KEY]
        ));
    }
}