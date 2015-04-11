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
     * @var array
     */
    protected $defaultDefinition = array(
        Entity::INNER_KEY => '{entity:roleName}_{entity:primaryKey}',
        Entity::OUTER_KEY => '{outer:roleName}_{outer:primaryKey}'
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
}