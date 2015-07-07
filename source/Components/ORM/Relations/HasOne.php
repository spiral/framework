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

    protected function createSelector()
    {
        $selector = parent::createSelector();

        if (isset($this->definition[ActiveRecord::MORPH_KEY]))
        {
            $selector->where($this->definition[ActiveRecord::MORPH_KEY], $this->parent->getRoleName());
        }

        $selector->where(
            $this->definition[ActiveRecord::OUTER_KEY],
            $this->parent->getField($this->definition[ActiveRecord::INNER_KEY], false)
        );

        return $selector;
    }

    public function getContent()
    {
        if (!$this->parent->isLoaded())
        {
            //Empty object
            return static::MULTIPLE ? [] : $this->orm->construct($this->getClass(), []);
        }

        return parent::getContent();
    }
}