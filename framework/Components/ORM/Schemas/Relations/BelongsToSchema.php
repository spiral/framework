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

class BelongsToSchema extends RelationSchema
{
    /**
     * Relation type.
     */
    const RELATION_TYPE = Entity::BELONGS_TO;

    /**
     * Equivalent relationship resolved based on definition and not schema, usually polymorphic.
     */
    const EQUIVALENT_RELATION = Entity::BELONGS_TO_MORPHED;

    public function initiate()
    {
        $this->define(
            Entity::FOREIGN_KEY, '{foreign:pK}'
        );

        echo $this->define(
            Entity::LOCAL_KEY, '{foreign:pK}_' . $this->definition[Entity::FOREIGN_KEY]
        );
    }
}