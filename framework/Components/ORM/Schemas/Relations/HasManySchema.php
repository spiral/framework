<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ORM\Schemas\Relations;

use Spiral\Components\ORM\ActiveRecord;

class HasManySchema extends HasOneSchema
{
    /**
     * Relation type.
     */
    const RELATION_TYPE = ActiveRecord::HAS_MANY;
}