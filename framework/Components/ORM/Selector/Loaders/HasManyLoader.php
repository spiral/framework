<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ORM\Selector\Loaders;

use Spiral\Components\ORM\Entity;
use Spiral\Components\ORM\Selector;

class HasManyLoader extends HasOneLoader
{
    /**
     * Relation type is required to correctly resolve foreign model.
     */
    const RELATION_TYPE = Entity::HAS_MANY;

    /**
     * Default load method (inload or postload).
     */
    const LOAD_METHOD = Selector::INLOAD; //TODO: change

    const MULTIPLE = true;
}