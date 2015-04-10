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

class MorphedHasOneSchema extends RelationshipSchema
{
    /**
     * Relationship type.
     */
    const RELATIONSHIP_TYPE = Entity::MORPHED_HAS_ONE;

    public function cast(EntitySchema $schema)
    {
    }
}