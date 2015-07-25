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
use Spiral\Core\Component\LoggerTrait;

class ManyToMany extends Relation implements \Countable
{
    /**
     * For warnings.
     */
    use LoggerTrait;

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
     * Attention, WHERE_PIVOT will not be used by default.
     *
     * Examples:
     * $user->tags()->has($tag);
     * $user->tags()->has([$tagA, $tagB]);
     * $user->tags()->has(1);
     * $user->tags()->has([1, 2, 3, 4]);
     *
     * @param mixed $modelID
     * @param bool  $wherePivot Use conditions specified by WHERE_PIVOT, disabled by default.
     * @return bool
     */
    public function has($modelID, $wherePivot = false)
    {
        $selectQuery = $this->pivotTable()->where(
            $this->wherePivot($this->innerKey(), $this->prepareIDs($modelID), $wherePivot)
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
     * @param bool  $wherePivot Use conditions specified by WHERE_PIVOT, disabled by default.
     * @return array
     */
    public function hasEach($modelIDs, $wherePivot = false)
    {
        $selectQuery = $this->pivotTable()->where(
            $this->wherePivot($this->innerKey(), $this->prepareIDs($modelIDs), $wherePivot)
        );

        $selectQuery->columns($this->definition[ActiveRecord::THOUGHT_OUTER_KEY]);

        $result = [];
        foreach ($selectQuery->run() as $row)
        {
            $result[] = $row[$this->definition[ActiveRecord::THOUGHT_OUTER_KEY]];
        }

        return $result;
    }

    /**
     * Link or update link for one of multiple related records. You can pass pivotData as additional
     * argument or associate it with model id. Attention! This method will not follow WHERE_PIVOT
     * conditions, you WILL have to specify them by yourself.
     *
     * Examples:
     * $user->tags->link(1);
     * $user->tags->link($tag);
     * $user->tags->link([1, 2], ['approved' => true]);
     * $user->tags->link([
     *      1 => ['approved' => true],
     *      2 => ['approved' => false]
     * ]);
     *
     * If record already linked it will be updated with provided pivot data, if you disable it by
     * providing third argument as true.
     *
     * @param mixed $modelID
     * @param array $pivotData
     * @param bool  $linkOnly If true no updates will be performed.
     * @return int
     */
    public function link($modelID, array $pivotData = [], $linkOnly = false)
    {
        //I need different method here
        $modelID = $this->prepareIDs($modelID, $pivotRows, $pivotData);
        $existedIDs = $this->hasEach($modelID);

        $result = 0;
        foreach ($pivotRows as $modelID => $pivotRow)
        {
            if (in_array($modelID, $existedIDs))
            {
                if (!$linkOnly)
                {
                    //We can update
                    $result += $this->pivotTable()->update(
                        $pivotRow,
                        $this->wherePivot($this->innerKey(), $modelID)
                    )->run();
                }
            }
            else
            {
                /**
                 * In future this statement should be optimized to use batchInsert in cases when
                 * set of columns for every record is the same.
                 */
                $this->pivotTable()->insert($pivotRow);

                $result++;
            }
        }

        return $result;
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
            $this->wherePivot($this->innerKey(), $this->prepareIDs($modelID), false)
        )->run();
    }

    /**
     * Unlink every associated record, method will return amount of affected rows. Method will unlink
     * only records matched WHERE_PIVOT by default. Set wherePivot to false to unlink every record.
     *
     * @param bool $wherePivot Use conditions specified by WHERE_PIVOT, enabled by default.
     * @return int
     */
    public function unlinkAll($wherePivot = true)
    {
        return $this->pivotTable()->delete(
            $this->wherePivot($this->innerKey(), null, $wherePivot)
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
     * @param bool        $wherePivot Use conditions specified by WHERE_PIVOT, disabled by default.
     * @return array
     */
    protected function wherePivot($innerKey, $outerKey, $wherePivot = false)
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

        if ($wherePivot && !empty($this->definition[ActiveRecord::WHERE_PIVOT]))
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
     * @param array $pivotRows Automatically constructed pivot rows will be available here for insertion
     *                         or update.
     * @param array $pivotData
     * @return mixed
     */
    protected function prepareIDs($modelID, array &$pivotRows = null, array $pivotData = [])
    {
        if (is_scalar($modelID))
        {
            $pivotRows = [$modelID => $this->pivotRow($modelID, $pivotData)];

            return $modelID;
        }

        if (is_array($modelID))
        {
            $result = [];
            foreach ($modelID as $key => $value)
            {
                if (is_scalar($value))
                {
                    $pivotRows[$value] = $this->pivotRow($value, $pivotData);
                    $result[] = $value;
                }
                else
                {
                    //Specified in key => pivotData format.
                    $pivotRows[$key] = $this->pivotRow($key, $value + $pivotData);
                    $result[] = $key;
                }
            }

            return $result;
        }

        if (is_object($modelID) && get_class($modelID) != $this->getClass())
        {
            throw new ORMException(
                "Relation can work only with instances of '{$this->getClass()}' model."
            );
        }

        $modelID = $modelID->getField($this->definition[ActiveRecord::OUTER_KEY]);

        //To be inserted later
        $pivotRows = [$modelID => $this->pivotRow($modelID, $pivotData)];

        return $modelID;
    }

    /**
     * Create data set to be inserted/updated into pivot table.
     *
     * @param mixed $outerKey
     * @param array $pivotData
     * @return array
     */
    protected function pivotRow($outerKey, array $pivotData = [])
    {
        $data = [
            $this->definition[ActiveRecord::THOUGHT_INNER_KEY] => $this->innerKey(),
            $this->definition[ActiveRecord::THOUGHT_OUTER_KEY] => $outerKey
        ];

        if (!empty($this->definition[ActiveRecord::MORPH_KEY]))
        {
            $data[$this->definition[ActiveRecord::MORPH_KEY]] = !empty($this->roleName)
                ? $this->roleName
                : $this->parent->getRoleName();
        }

        return $data + $pivotData;
    }
}