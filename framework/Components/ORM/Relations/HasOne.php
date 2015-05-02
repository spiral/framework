<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ORM\Relations;

use Spiral\Components\ORM\Entity;
use Spiral\Components\ORM\ORM;
use Spiral\Components\ORM\Relation;
use Spiral\Components\ORM\Selector;

class HasOne extends Relation
{
    const DEFAULT_LOADER = Selector::INLOAD;

    //    public function createInload($parentTable, Selector $selector)
    //    {
    //        //TODO: RELATION NAME
    //
    //        $outerKey = $this->definition[self::OUTER_TABLE] . '.' . $this->definition[Entity::OUTER_KEY];
    //
    //        //Inner key has to be build based on parent table
    //        $innerKey = $this->definition[Entity::INNER_KEY];
    //
    //        $selector->leftJoin($this->definition[self::OUTER_TABLE], array(
    //            $outerKey => $parentTable . '.' . $innerKey
    //        ));
    //    }
}