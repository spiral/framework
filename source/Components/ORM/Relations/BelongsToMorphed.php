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
use Spiral\Components\ORM\ORMException;
use Spiral\Components\ORM\Selector;

class BelongsToMorphed extends BelongsTo
{
    /**
     * Relation type.
     */
    const RELATION_TYPE = ActiveRecord::BELONGS_TO_MORPHED;

    /**
     * Morphed class.
     *
     * @return string
     */
    protected function getMorphedClass()
    {
        $morphKey = $this->definition[ActiveRecord::MORPH_KEY];

        return $this->getClass()[$this->parent->getField($morphKey)];
    }

    /**
     * Convert pre-loaded relation data to active record model or set of models.
     *
     * @return ModelIterator|ActiveRecord
     */
    protected function createModel()
    {
        return $this->instance = $this->orm->construct($this->getMorphedClass(), $this->data);
    }

    /**
     * Internal ORM relation method used to create valid selector used to pre-load relation data or
     * create custom query based on relation options.
     *
     * @return Selector
     */
    protected function createSelector()
    {
        $selector = new Selector($this->getMorphedClass(), $this->orm);

        return $selector->where(
            $selector->getPrimaryAlias() . '.' . $this->definition[ActiveRecord::OUTER_KEY],
            $this->parent->getField($this->definition[ActiveRecord::INNER_KEY], false)
        );
    }

    /**
     * Set relation data (called via __set method of parent ActiveRecord).
     *
     * Example:
     * $user->profile = new Profile();
     *
     * @param mixed $data
     * @throws ORMException
     */
    public function setData($data)
    {
        parent::setData($data);

        //Forcing morph key
        $morphKey = $this->definition[ActiveRecord::MORPH_KEY];
        $this->parent->setField($morphKey, $data->getRoleName(), false);
    }
}