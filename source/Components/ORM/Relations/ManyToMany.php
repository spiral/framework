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
use Spiral\Components\ORM\ORMException;
use Spiral\Components\ORM\Relation;
use Spiral\Components\ORM\Selector;

class ManyToMany extends Relation implements \Countable
{
    /**
     * Relation type.
     */
    const RELATION_TYPE = ActiveRecord::MANY_TO_MANY;

    /**
     * Indication that relation represent multiple records.
     */
    const MULTIPLE = true;

    /**
     * Target role name, by default parent role name.
     *
     * @var string
     */
    protected $roleName = '';

    /**
     * Force relation role name (for morphed relations only).
     *
     * @param string $roleName
     */
    public function setRoleName($roleName)
    {
        $this->roleName = $roleName;
    }

    /**
     * Internal ORM relation method used to create valid selector used to pre-load relation data or
     * create custom query based on relation options.
     *
     * @return Selector
     */
    protected function createSelector()
    {
        //For Many-to-Many relation we have to use custom loader to parse data, this is ONLY for
        //this type of relation
        $loader = new Selector\Loaders\ManyToManyLoader($this->orm, '', $this->definition);

        //Sometimes we have to force different role name (especially for morphed relations)
        $roleName = !empty($this->roleName) ? $this->roleName : $this->parent->getRoleName();

        return $loader->createSelector($roleName)->where(
            $loader->getPivotAlias() . '.' . $this->definition[ActiveRecord::THOUGHT_INNER_KEY],
            $this->innerKey()
        );
    }

    /**
     * Mount relation keys to parent or children models to ensure their connection. Method called
     * when model requests relation save.
     *
     * @param ActiveRecord $model
     * @return ActiveRecord
     */
    protected function mountRelation(ActiveRecord $model)
    {
        //Nothing to do, every fetched model should be already linked
        return $model;
    }

    /**
     * Parent model inner key.
     *
     * @return mixed
     */
    protected function innerKey()
    {
        return $this->parent->getField($this->definition[ActiveRecord::INNER_KEY]);
    }

    /**
     * Count method will work with pivot table directly.
     *
     * @return int
     */
    public function count()
    {
        return $this->pivotTable()->where(
            $this->wherePivot($this->innerKey(), null)
        )->count();
    }

    /**
     * Check if ActiveRecord(s) associated with this relation. Method can accept one id, array of ids,
     * or instance of ActiveRecord. In case of multiple ids provided method will return true only
     * if every model is linked to relation.
     *
     * Examples:
     * $user->tags()->has($tag);
     * $user->tags()->has([$tagA, $tagB]);
     * $user->tags()->has(1);
     * $user->tags()->has([1, 2, 3, 4]);
     *
     * @param mixed $modelID
     * @return bool
     */
    public function has($modelID)
    {
        $selectQuery = $this->pivotTable()->where(
            $this->wherePivot($this->innerKey(), $this->prepareIDs($modelID))
        );

        //We can use hasEach methods there, but this is more optimal way
        return $selectQuery->count() == count($modelID);
    }

    /**
     * Return only list of ids which are linked.
     *
     * Examples:
     * $user->tags()->hasEach($tag);
     * $user->tags()->hasEach([$tagA, $tagB]);
     * $user->tags()->hasEach(1);
     * $user->tags()->hasEach([1, 2, 3, 4]);
     *
     * @param mixed $modelIDs
     * @return array
     */
    public function hasEach($modelIDs)
    {
        $selectQuery = $this->pivotTable()->where(
            $this->wherePivot($this->innerKey(), $this->prepareIDs($modelIDs))
        );

        $selectQuery->columns($this->definition[ActiveRecord::THOUGHT_OUTER_KEY]);

        $result = [];
        foreach ($selectQuery->run() as $row)
        {
            $result[] = $row[$this->definition[ActiveRecord::THOUGHT_OUTER_KEY]];
        }

        return $result;
    }

    public function link($modelID, array $pivotData = [])
    {

        $modelID = $this->prepareIDs($modelID);

        dump($modelID);
    }

    /**
     * Method used to unlink one of multiple associated ActiveRecords, method can accept id, list of
     * ids or instance of ActiveRecord. Method will return count of affected rows.
     *
     * Examples:
     * $user->tags()->unlink($tag);
     * $user->tags()->unlink([$tagA, $tagB]);
     * $user->tags()->unlink(1);
     * $user->tags()->unlink([1, 2, 3, 4]);
     *
     * @param mixed $modelID
     * @return int
     */
    public function unlink($modelID)
    {
        return $this->pivotTable()->delete(
            $this->wherePivot($this->innerKey(), $this->prepareIDs($modelID))
        )->run();
    }

    /**
     * Unlink every associated record, method will return amount of affected rows.
     *
     * @return int
     */
    public function unlinkAll()
    {
        return $this->pivotTable()->delete(
            $this->wherePivot($this->innerKey(), null)
        )->run();
    }

    /**
     * Instance of DBAL\Table associated with relation pivot table.
     *
     * @return \Spiral\Components\DBAL\Table
     */
    protected function pivotTable()
    {
        return $this->parent->dbalDatabase($this->orm)->table(
            $this->definition[ActiveRecord::PIVOT_TABLE]
        );
    }

    /**
     * Helper method used to create valid WHERE query for delete and update in pivot table.
     *
     * @param mixed|array $innerKey
     * @param mixed|array $outerKey
     * @return array
     */
    protected function wherePivot($innerKey, $outerKey)
    {
        $query = [];
        if (!empty($this->definition[ActiveRecord::MORPH_KEY]))
        {
            $query[$this->definition[ActiveRecord::MORPH_KEY]] = !empty($this->roleName)
                ? $this->roleName
                : $this->parent->getRoleName();
        }

        if (!empty($innerKey))
        {
            $query[$this->definition[ActiveRecord::THOUGHT_INNER_KEY]] = $innerKey;
        }

        if (!empty($this->definition[ActiveRecord::WHERE_PIVOT]))
        {
            $query = $query + $this->definition[ActiveRecord::WHERE_PIVOT];
        }

        if (!empty($outerKey))
        {
            $query[$this->definition[ActiveRecord::THOUGHT_OUTER_KEY]] = is_array($outerKey)
                ? ['IN' => $outerKey]
                : $outerKey;
        }

        return $query;
    }

    /**
     * Helper method to fetch outer key value from provided list.
     *
     * @param mixed $modelID
     * @return mixed
     */
    protected function prepareIDs($modelID)
    {
        if (is_scalar($modelID))
        {
            return $modelID;
        }

        if (is_array($modelID))
        {
            return array_map([$this, 'prepareIDs'], $modelID);
        }

        if (is_object($modelID) && get_class($modelID) != $this->getClass())
        {
            throw new ORMException(
                "Relation can work only with instances of '{$this->getClass()}' model."
            );
        }

        return $modelID->getField($this->definition[ActiveRecord::OUTER_KEY]);
    }
}