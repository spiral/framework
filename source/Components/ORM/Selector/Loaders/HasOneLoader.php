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
use Spiral\Components\ORM\Relation;
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

        //Aggregated keys (example: all parent ids)
        $aggregatedKeys = $this->parent->getAggregatedKeys($this->getReferenceKey());

        if (empty($aggregatedKeys))
        {
            //Nothing to postload, no parents
            return null;
        }

        //Adding condition
        $selector->where(
            $this->getAlias() . '.' . $this->definition[ActiveRecord::OUTER_KEY],
            'IN',
            array_unique($aggregatedKeys)
        );

        if (!empty($this->definition[ActiveRecord::MORPH_KEY]))
        {
            $morphKey = $this->getAlias() . '.' . $this->definition[ActiveRecord::MORPH_KEY];
            $selector->where([$morphKey => $this->parent->schema[ORM::E_ROLE_NAME]]);
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
        $outerKey = $this->getAlias() . '.' . $this->definition[ActiveRecord::OUTER_KEY];

        //Inner key has to be build based on parent table
        $innerKey = $this->parent->getAlias() . '.' . $this->definition[ActiveRecord::INNER_KEY];

        $selector->leftJoin(
            $this->definition[Relation::OUTER_TABLE] . ' AS ' . $this->getAlias(),
            [$outerKey => $innerKey]
        );

        if (!empty($this->definition[ActiveRecord::MORPH_KEY]))
        {
            $morphKey = $this->getAlias() . '.' . $this->definition[ActiveRecord::MORPH_KEY];
            $selector->onWhere([$morphKey => $this->parent->schema[ORM::E_ROLE_NAME]]);
        }
    }
}