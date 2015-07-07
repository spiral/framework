<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ORM\Selector\Loaders;

use Spiral\Components\ORM\ActiveRecord;
use Spiral\Components\ORM\ORM;
use Spiral\Components\ORM\Selector;
use Spiral\Components\ORM\Selector\Loader;

class HasOneLoader extends Loader
{
    /**
     * Relation type is required to correctly resolve foreign model.
     */
    const RELATION_TYPE = ActiveRecord::HAS_ONE;

    /**
     * Default load method (inload or postload).
     */
    const LOAD_METHOD = Selector::INLOAD;

    /**
     * Internal loader constant used to decide nested aggregation level.
     */
    const MULTIPLE = false;

    /**
     * Create selector to be executed as post load, usually such selector use aggregated values
     * and IN where syntax.
     *
     * @return Selector
     */
    public function createSelector()
    {
        if (empty($selector = parent::createSelector()))
        {
            return null;
        }

        if (empty($this->parent))
        {
            //No need for where conditions
            return $selector;
        }

        //Mounting where conditions
        $this->mountConditions($selector);

        //Aggregated keys (example: all parent ids)
        if (empty($aggregatedKeys = $this->parent->getAggregatedKeys($this->getReferenceKey())))
        {
            //Nothing to postload, no parents
            return null;
        }

        //Adding condition
        $selector->where($this->getKey(ActiveRecord::OUTER_KEY), 'IN', $aggregatedKeys);

        return $selector;
    }

    /**
     * ORM Loader specific method used to clarify selector conditions, join and columns with
     * loader specific information.
     *
     * @param Selector $selector
     */
    protected function clarifySelector(Selector $selector)
    {
        $selector->join($this->joinType(), $this->getTable() . ' AS ' . $this->getAlias(), [
            $this->getKey(ActiveRecord::OUTER_KEY) => $this->getParentKey()
        ]);

        $this->mountConditions($selector);
    }

    /**
     * Set morph key and additional where conditions to selector.
     *
     * @param Selector $selector
     * @return Selector
     */
    protected function mountConditions(Selector $selector)
    {
        if (!empty($morphKey = $this->getKey(ActiveRecord::MORPH_KEY)))
        {
            if ($this->isJoined())
            {
                $selector->onWhere($morphKey, $this->parent->schema[ORM::E_ROLE_NAME]);
            }
            else
            {
                $selector->where($morphKey, $this->parent->schema[ORM::E_ROLE_NAME]);
            }
        }

        return $selector;
    }
}