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

class HasOne extends Relation
{
    /**
     * Relation type.
     */
    const RELATION_TYPE = ActiveRecord::HAS_ONE;

    /**
     * Internal ORM relation method used to create valid selector used to pre-load relation data or
     * create custom query based on relation options.
     *
     * @return Selector
     */
    protected function createSelector()
    {
        $selector = parent::createSelector();

        if (isset($this->definition[ActiveRecord::MORPH_KEY]))
        {
            $selector->where(
                $selector->getPrimaryAlias() . '.' . $this->definition[ActiveRecord::MORPH_KEY],
                $this->parent->getRoleName()
            );
        }

        $selector->where(
            $selector->getPrimaryAlias() . '.' . $this->definition[ActiveRecord::OUTER_KEY],
            $this->parent->getField($this->definition[ActiveRecord::INNER_KEY], false)
        );

        return $selector;
    }

    /**
     * Set relation data (called via __set method of parent ActiveRecord).
     *
     * Example:
     * $user->profile = new Profile();
     *
     * @param ActiveRecord $instance
     * @throws ORMException
     */
    public function setInstance(ActiveRecord $instance = null)
    {
        parent::setInstance($instance);
        $this->mountRelation($instance);
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
        //Key in child model
        $outerKey = $this->definition[ActiveRecord::OUTER_KEY];

        //Key in parent model
        $innerKey = $this->definition[ActiveRecord::INNER_KEY];

        if ($model->getField($outerKey, false) != $this->parent->getField($innerKey, false))
        {
            $model->setField($outerKey, $this->parent->getField($innerKey, false), false);
        }

        if (!isset($this->definition[ActiveRecord::MORPH_KEY]))
        {
            //No morph key presented
            return $model;
        }

        $morphKey = $this->definition[ActiveRecord::MORPH_KEY];

        if ($model->getField($morphKey) != $this->parent->getRoleName())
        {
            $model->setField($morphKey, $this->parent->getRoleName());
        }

        return $model;
    }

    /**
     * Create model and configure it's fields with relation data. Attention, you have to validate and
     * save record by your own.
     *
     * @param mixed $fields
     * @return ActiveRecord
     */
    public function create($fields = [])
    {
        $model = call_user_func([$this->getClass(), 'create'], $fields);

        return $this->mountRelation($model);
    }
}