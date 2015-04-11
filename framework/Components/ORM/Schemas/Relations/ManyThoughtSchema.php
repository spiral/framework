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
        Entity::LOCAL_KEY   => '{entity:roleName}_{entity:primaryKey}',
        Entity::OUTER_KEY => '{foreign:roleName}_{foreign:primaryKey}'
    );

    public function buildSchema()
    {
        if (empty($this->definition[Entity::PIVOT_TABLE]))
        {
            //WE NEED IT!
        }
    }
}