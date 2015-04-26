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

    public function inload($parentTable, Selector $selector, ORM $orm)
    {
        $outerSchema = $orm->getSchema($this->target);
        //TODO: RELATION NAME

        $outerKey = $outerSchema[ORM::E_TABLE] . '.' . $this->definition[Entity::OUTER_KEY];

        //Inner key has to be build based on parent table
        $innerKey = $this->definition[Entity::INNER_KEY];

        $selector->leftJoin($outerSchema[ORM::E_TABLE], array(
            $outerKey => $parentTable . '.' . $innerKey
        ));
    }
}