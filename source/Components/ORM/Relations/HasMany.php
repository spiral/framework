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

class HasMany extends Relation
{
    const RELATION_TYPE = ActiveRecord::HAS_MANY;

    /**
     * @return mixed
     */
    public function getContent()
    {
        if (is_object($this->data))
        {
            return $this->data;
        }

        $class = $this->definition[static::RELATION_TYPE];
        //        if (!$this->parent->isLoaded())
        //        {
        //            return $this->data = new $targetClass([], false, $this->orm);
        //        }
        //
        if ($this->data === null)
        {
            $this->loadData();
        }

        return $this->data = new ModelIterator($this->orm, $class, $this->data);
    }

    protected function loadData()
    {
        $selector = $this->createSelector();

        //We have to configure where conditions

        dump($selector);
    }
}