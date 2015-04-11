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

class MorphedManyToManySchema extends RelationSchema
{
    /**
     * Relation type.
     */
    const RELATION_TYPE = Entity::MORPHED_MANY_TO_MANY;

    public function buildSchema()
    {
    }
}