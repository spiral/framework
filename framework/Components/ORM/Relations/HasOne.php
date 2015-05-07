<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ORM\Relations;

use Spiral\Components\ORM\ActiveRecord;
use Spiral\Components\ORM\ORM;
use Spiral\Components\ORM\Relation;
use Spiral\Components\ORM\Selector;

class HasOne extends Relation
{
    const RELATION_TYPE = ActiveRecord::HAS_ONE;

    /**
     * @return mixed
     */
    public function getContent()
    {
        //if (!$this->parent->isLoaded())
        //{
        //has to be handled
        //we have to create model manually
        //}

        if ($this->data === null)
        {
            $this->loadData();
        }
    }

    public function getSelector()
    {
        if (!$this->parent->isLoaded())
        {
            return null;
        }

        //TODO: Outer table to function

        $selector = parent::getSelector();
        $selector->where(
            $this->definition[self::OUTER_TABLE] . '.' . $this->definition[ActiveRecord::OUTER_KEY],
            $this->parent->getField($this->definition[ActiveRecord::INNER_KEY], false)
        );

        return $selector;
    }
}