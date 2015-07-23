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
use Spiral\Components\ORM\ModelIterator;
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
            $selector->where(
                $this->definition[ActiveRecord::MORPH_KEY],
                $this->parent->getRoleName()
            );
        }

        $selector->where(
            $this->definition[ActiveRecord::OUTER_KEY],
            $this->parent->getField($this->definition[ActiveRecord::INNER_KEY], false)
        );

        return $selector;
    }

    public function getContent()
    {
        if (empty($this->data) && !$this->parent->isLoaded())
        {
            if (static::MULTIPLE)
            {
                return $this->data = new ModelIterator($this->orm, $this->getClass(), []);
            }

            //Empty object
            return $this->data = $this->orm->construct($this->getClass(), []);
        }

        return parent::getContent();
    }
}