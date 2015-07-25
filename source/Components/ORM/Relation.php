<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ORM;

use Spiral\Support\Models\DataEntity;

abstract class Relation implements RelationInterface, \Countable, \IteratorAggregate, \JsonSerializable
{
    /**
     * Relation type.
     */
    const RELATION_TYPE = ActiveRecord::HAS_ONE;

    /**
     * Indication that relation represent multiple records.
     */
    const MULTIPLE = false;

    /**
     * ORM component.
     *
     * @invisible
     * @var ORM
     */
    protected $orm = null;

    /**
     * Parent ActiveRecord used to supply valid values for foreign keys and etc. In some cases active
     * record can be updated by relation (for example in cases of BELONG_TO assignment).
     *
     * @var ActiveRecord
     */
    protected $parent = null;

    /**
     * Relation definition fetched from ORM schema.
     *
     * @invisible
     * @var array
     */
    protected $definition = [];

    /**
     * Pre-loaded relation data, can be loaded while parent model, or later. Real active-record/model
     * iterator will be constructed at moment of data access.
     *
     * @var array|null
     */
    protected $data = [];

    /**
     * Instance of constructed ActiveRecord of ModelIterator.
     *
     * @invisible
     * @var ActiveRecord|ModelIterator
     */
    protected $instance = null;

    /**
     * Indication that relation data has been loaded from databases.
     *
     * @var bool
     */
    protected $loaded = false;

    /**
     * New instance of ORM relation, relations used to represent queries and pre-loaded data inside
     * parent active record, relations by itself not used in query building - but they can be used
     * to create valid query selector.
     *
     * @param ORM          $orm        ORM component.
     * @param ActiveRecord $parent     Parent ActiveRecord object.
     * @param array        $definition Relation definition.
     * @param mixed        $data       Pre-loaded relation data.
     * @param bool         $loaded     Indication that relation data has been loaded.
     */
    public function __construct(
        ORM $orm,
        ActiveRecord $parent,
        array $definition,
        $data = null,
        $loaded = false
    )
    {
        $this->orm = $orm;
        $this->parent = $parent;
        $this->definition = $definition;
        $this->data = $data;
        $this->loaded = $loaded;
    }

    /**
     * Class name of outer model.
     *
     * @return string
     */
    protected function getClass()
    {
        return $this->definition[static::RELATION_TYPE];
    }

    /**
     * Reset relation pre-loaded data. By default will flush realtion data.
     *
     * @param mixed $data   Pre-loaded relation data.
     * @param bool  $loaded Indication that relation data has been loaded.
     */
    public function reset(array $data = [], $loaded = false)
    {
        if (!empty($this->data) && $this->data == $data)
        {
            //Nothing to do, context is the same
            return;
        }

        if (!$loaded || !($this->instance instanceof ActiveRecord))
        {
            //Flushing instance
            $this->instance = null;
        }

        $this->data = $data;
        $this->loaded = $loaded;
    }

    /**
     * Check if relation was loaded (even empty).
     *
     * @return bool
     */
    public function isLoaded()
    {
        return $this->loaded;
    }

    /**
     * Get relation data (data should be automatically loaded if not pre-loaded already). Result
     * can vary based on relation type and usually represent one model or array of models.
     *
     * @return array|null|DataEntity|DataEntity[]
     */
    public function getInstance()
    {
        if (!empty($this->instance))
        {
            if ($this->instance instanceof ActiveRecord && !empty($this->data))
            {
                $this->instance->setContext($this->data);
            }

            //Already constructed (but we have to update the context)
            return $this->instance;
        }

        //Loading data if not already loaded
        !$this->isLoaded() && $this->loadData();

        if (empty($this->data))
        {
            //Can not be loaded
            return static::MULTIPLE ? new ModelIterator($this->orm, $this->getClass(), []) : null;
        }

        return $this->instance = (static::MULTIPLE ? $this->createIterator() : $this->createModel());
    }

    /**
     * Convert pre-loaded relation data to model iterator model.
     *
     * @return ModelIterator
     */
    protected function createIterator()
    {
        return new ModelIterator($this->orm, $this->getClass(), $this->data);
    }

    /**
     * Convert pre-loaded relation data to active record model .
     *
     * @return ActiveRecord
     */
    protected function createModel()
    {
        return $this->orm->construct($this->getClass(), $this->data);
    }

    /**
     * Internal ORM relation method used to create valid selector used to pre-load relation data or
     * create custom query based on relation options.
     *
     * @return Selector
     */
    protected function createSelector()
    {
        return new Selector($this->getClass(), $this->orm);
    }

    /**
     * Bypassing call to created selector.
     *
     * @param string $method
     * @param array  $arguments
     * @return mixed
     */
    public function __call($method, array $arguments)
    {
        return call_user_func_array([$this->createSelector(), $method], $arguments);
    }

    /**
     * Invoke relation with custom arguments. Result may vary based on relation logic.
     *
     * @param array $arguments
     * @return mixed
     */
    public function __invoke(array $arguments)
    {
        return $this->createSelector()->where($arguments);
    }

    /**
     * Bypassing count call, required for implementing Countable interface.
     *
     * @return int
     */
    public function count()
    {
        return $this->createSelector()->count();
    }

    /**
     * Bypassing getIterator call, required for implementing IteratorAggregate interface.
     *
     * @return ActiveRecord[]|ModelIterator
     */
    public function getIterator()
    {
        return $this->createSelector()->getIterator();
    }

    /**
     * Load relation data based on created selector.
     *
     * @return array|null
     */
    protected function loadData()
    {
        if (!$this->parent->isLoaded())
        {
            return null;
        }

        $this->loaded = true;
        if (static::MULTIPLE)
        {
            return $this->data = $this->createSelector()->fetchData();
        }

        $data = $this->createSelector()->fetchData();
        if (isset($data[0]))
        {
            return $this->data = $data[0];
        }

        return null;
    }

    /**
     * Set relation instance (called via __set method of parent ActiveRecord).
     *
     * Example:
     * $user->profile = new Profile();
     *
     * @param ActiveRecord $instance
     * @throws ORMException
     */
    public function setInstance(ActiveRecord $instance)
    {
        if (static::MULTIPLE)
        {
            throw new ORMException(
                "Unable to assign relation data (relation represent multiple records)."
            );
        }

        if (!is_array($allowed = $this->getClass()))
        {
            $allowed = [$allowed];
        }

        if (!is_object($instance) || !in_array(get_class($instance), $allowed))
        {
            $allowed = join("', '", $allowed);

            throw new ORMException(
                "Only instances of '{$allowed}' can be assigned to this relation."
            );
        }

        //Entity caching
        $this->instance = $instance;
        $this->loaded = true;
    }

    /**
     * ActiveRecord may ask relation data to be saved, save content will work ONLY for pre-loaded
     * relation content. This method better not be called outside of active record.
     *
     * @param bool $validate
     * @return bool
     */
    public function saveInstance($validate = true)
    {
        if (empty($instance = $this->getInstance()))
        {
            //Nothing to save
            return true;
        }

        if (static::MULTIPLE)
        {
            /**
             * @var ActiveRecord[] $instance
             */
            foreach ($instance as $model)
            {
                if ($model->isDeleted())
                {
                    continue;
                }

                if (!$this->mountRelation($model)->save($validate, true))
                {
                    return false;
                }

                $this->orm->registerEntity($model);
            }

            return true;
        }

        /**
         * @var ActiveRecord $instance
         */
        if ($instance->isDeleted())
        {
            return true;
        }

        if (!$this->mountRelation($instance)->save($validate, true))
        {
            return false;
        }

        $this->orm->registerEntity($instance);

        return true;
    }

    /**
     * Get relation data errors (if any).
     *
     * @param bool $reset
     * @return mixed
     */
    public function getErrors($reset = false)
    {
        if (static::MULTIPLE)
        {
            /**
             * @var ActiveRecord[] $data
             */
            $errors = [];
            foreach ($data as $position => $model)
            {
                if (!$model->isValid())
                {
                    $errors[$position] = $model->getErrors(true);
                }
            }

            return $errors;
        }

        return $this->getInstance()->getErrors($reset);
    }

    /**
     * Mount relation keys to parent or children models to ensure their connection. Method called
     * when model requests relation save.
     *
     * @param ActiveRecord $model
     * @return ActiveRecord
     */
    abstract protected function mountRelation(ActiveRecord $model);

    /**
     * (PHP 5 > 5.4.0)
     * Specify data which should be serialized to JSON.
     *
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed
     */
    public function jsonSerialize()
    {
        return $this->getInstance();
    }
}
