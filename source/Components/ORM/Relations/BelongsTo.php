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

class BelongsTo extends HasOne
{
    /**
     * Relation type.
     */
    const RELATION_TYPE = ActiveRecord::BELONGS_TO;

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
        if (is_null($instance))
        {
            $this->dropRelation();

            return;
        }

        parent::setInstance($instance);

        /**
         * @var ActiveRecord $instance
         */
        if (!$instance->isLoaded())
        {
            throw new ORMException(
                "Unable to set 'belongs to' parent, parent has be fetched from database."
            );
        }

        //Key in parent model
        $outerKey = $this->definition[ActiveRecord::OUTER_KEY];

        //Key in child model
        $innerKey = $this->definition[ActiveRecord::INNER_KEY];

        if ($this->parent->getField($innerKey, false) != $instance->getField($outerKey, false))
        {
            //We are going to set relation keys right on assertion
            $this->parent->setField($innerKey, $instance->getField($outerKey, false), false);
        }
    }

    /**
     * Drop relation keys.
     */
    protected function dropRelation()
    {
        $innerKey = $this->definition[ActiveRecord::INNER_KEY];
        $this->parent->setField($innerKey, null, false);
    }

    /**
     * Mount relation keys to parent or children models to ensure their connection.
     *
     * @param ActiveRecord $model
     * @return ActiveRecord
     */
    protected function mountRelation(ActiveRecord $model)
    {
        //Nothing to do, children can not update parent relation
        return $model;
    }
}