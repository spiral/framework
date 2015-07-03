<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ORM\Selector\Loaders;

use Spiral\Components\DBAL\Database;
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

    const MULTIPLE = false;

    protected function clarifyQuery(Selector $selector)
    {
        //Relation definition
        $definition = $this->definition;

        $outerKey = $this->getAlias() . '.' . $definition[ActiveRecord::OUTER_KEY];

        //Inner key has to be build based on parent table
        $innerKey = $this->parent->getAlias() . '.' . $definition[ActiveRecord::INNER_KEY];

        $selector->leftJoin(
            $definition[Relation::OUTER_TABLE] . ' AS ' . $this->getAlias(),
            [$outerKey => $innerKey]
        );

        if (!empty($this->definition[ActiveRecord::MORPH_KEY]))
        {
            $morphKey = $this->getAlias() . '.' . $definition[ActiveRecord::MORPH_KEY];
            $selector->onWhere([
                $morphKey => $this->parent->schema[ORM::E_ROLE_NAME]
            ]);
        }
    }

    public function parseRow(array $row)
    {
        if (!$this->isLoaded())
        {
            return;
        }

        $data = $this->fetchData($row);

        if (!$referenceName = $this->getReferenceName($data))
        {
            //Relation not loaded
            return;
        }

        //WHAT IF?
        if (!$this->checkDuplicate($data))
        {
            //Clarifying parent dataset
            $this->registerReferences($data);
            $this->parent->registerNested($referenceName, $this->container, $data, static::MULTIPLE);
        }

        $this->parseNested($row);
    }

    public function createSelector()
    {
        $selector = parent::createSelector();

        //Relation definition
        $definition = $this->definition;

        //Aggregated keys
        $aggregatedKeys = $this->parent->getAggregatedKeys($this->getReferenceKey());

        if (empty($aggregatedKeys))
        {
            //Nothing to postload, no parents
            return null;
        }

        //Adding condition
        $selector->where(
            $this->getAlias() . '.' . $definition[ActiveRecord::OUTER_KEY],
            'IN',
            array_unique($aggregatedKeys)
        );

        if (!empty($this->definition[ActiveRecord::MORPH_KEY]))
        {
            $morphKey = $this->getAlias() . '.' . $definition[ActiveRecord::MORPH_KEY];
            $selector->where([
                $morphKey => $this->parent->schema[ORM::E_ROLE_NAME]
            ]);
        }

        return $selector;
    }
}