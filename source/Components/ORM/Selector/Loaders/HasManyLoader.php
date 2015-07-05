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
use Spiral\Components\ORM\Selector;

class HasManyLoader extends HasOneLoader
{
    /**
     * Relation type is required to correctly resolve foreign model.
     */
    const RELATION_TYPE = ActiveRecord::HAS_MANY;

    /**
     * Default load method (inload or postload).
     */
    const LOAD_METHOD = Selector::POSTLOAD;

    /**
     * Internal loader constant used to decide nested aggregation level.
     */
    const MULTIPLE = true;

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

        if (!empty($this->definition[ActiveRecord::WHERE]))
        {
            $selector->where($this->prepareWhere(
                $this->definition[ActiveRecord::WHERE], $this->getAlias()
            ));
        }

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
        parent::clarifySelector($selector);

        if (!empty($this->definition[ActiveRecord::WHERE]))
        {
            $selector->onWhere($this->prepareWhere(
                $this->definition[ActiveRecord::WHERE], $this->getAlias()
            ));
        }
    }
}