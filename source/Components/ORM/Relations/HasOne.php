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
use Spiral\Components\ORM\Relation;
use Spiral\Components\ORM\Selector;

class HasOne extends Relation
{
    const RELATION_TYPE = ActiveRecord::HAS_ONE;

    protected $content = null;

    /**
     * @return mixed
     */
    public function getContent()
    {
        $target = $this->getTarget();

        if (!$this->parent->isLoaded())
        {
            return $this->data = new $target([], $this->orm);
        }

        if ($this->data === null)
        {
            $this->loadData();
        }

        if (is_object($this->data))
        {
            return $this->data;
        }

        return $this->data = new $target($this->data, $this->orm);
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