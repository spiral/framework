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
use Spiral\Components\ORM\ORM;
use Spiral\Components\ORM\ORMException;
use Spiral\Components\ORM\RelationInterface;
use Spiral\Components\ORM\Selector;

class ManyToMorphed implements RelationInterface
{
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
     * Set of nested relations aggregated by it's type.
     *
     * @var ManyToMany[]
     */
    protected $relations = [];

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
    }

    /**
     * Reset relation pre-loaded data. By default will flush relation data.
     *
     * @param mixed $data   Pre-loaded relation data.
     * @param bool  $loaded Indication that relation data has been loaded.
     */
    public function reset(array $data = [], $loaded = false)
    {
        foreach ($this->relations as $relation)
        {
            //Can be only flushed
            $relation->reset([], false);
        }

        //Dropping relations
        $this->relations = [];
    }

    /**
     * Check if relation was loaded (even empty).
     *
     * @return bool
     */
    public function isLoaded()
    {
        //Never loader
        return false;
    }

    /**
     * Get relation data (data should be automatically loaded if not pre-loaded already). Result
     * can vary based on relation type and usually represent one model or array of models.
     *
     * Morphed relation are not allowing direct data access.
     *
     * @return $this
     */
    public function getInstance()
    {
        return $this;
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
    public function setInstance(ActiveRecord $instance)
    {
        throw new ORMException("Unable to set data for morphed relation.");
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
        foreach ($this->relations as $relation)
        {
            if (!$relation->saveInstance($validate))
            {
                return false;
            }
        }

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
        $result = [];
        foreach ($this->relations as $alias => $relation)
        {
            if (!empty($errors = $relation->getErrors()))
            {
                $result[$alias] = $errors;
            }
        }

        return $result;
    }

    /**
     * Invoke relation with custom arguments. Result may vary based on relation logic.
     *
     * @param array $arguments
     * @return mixed
     */
    public function __invoke(array $arguments)
    {
        return $this;
    }

    /**
     * Get nested-relation associated with one of model aliases.
     *
     * @param string $alias
     * @return ManyToMany
     */
    protected function nestedRelation($alias)
    {
        if (isset($this->relations[$alias]))
        {
            return $this->relations[$alias];
        }

        if (!isset($this->definition[ActiveRecord::MORPHED_ALIASES][$alias]))
        {
            throw new ORMException("No such sub-relation or method '{$alias}'.");
        }

        //We have to create custom definition
        $definition = $this->definition;

        $roleName = $this->definition[ActiveRecord::MORPHED_ALIASES][$alias];
        $definition[ActiveRecord::MANY_TO_MANY] = $definition[ActiveRecord::MANY_TO_MORPHED][$roleName];

        unset($definition[ActiveRecord::MANY_TO_MORPHED], $definition[ActiveRecord::MORPHED_ALIASES]);

        //Creating many-to-many relation
        $this->relations[$alias] = new ManyToMany($this->orm, $this->parent, $definition);

        //We have to force role name
        $this->relations[$alias]->setRoleName($roleName);

        return $this->relations[$alias];
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
     * Count method will work with pivot table directly.
     *
     * @return int
     */
    public function count()
    {
        $innerKey = $this->definition[ActiveRecord::INNER_KEY];

        return $this->pivotTable()->where([
            $this->definition[ActiveRecord::THOUGHT_INNER_KEY] => $this->parent->getField($innerKey)
        ])->count();
    }

    /**
     * Get access to data instance stored in nested relation.
     *
     * Example:
     * $tag->tagged->users;
     * $tag->tagged->posts;
     *
     * @param string $alias
     * @return ActiveRecord|ModelIterator
     */
    public function __get($alias)
    {
        return $this->nestedRelation($alias)->getInstance();
    }

    /**
     * Get access to sub relation.
     *
     * Example:
     * $tag->tagged->users()->count(); //Without preloading
     * foreach($tag->tagged->users(["status" => "active"]) as $user)
     * {
     * }
     *
     * @param string $alias
     * @param array  $arguments
     * @return ManyToMany
     */
    public function __call($alias, array $arguments)
    {
        if (!empty($arguments))
        {
            return call_user_func_array($this->nestedRelation($alias), $arguments);
        }

        return $this->nestedRelation($alias);
    }


    public function link(ActiveRecord $record, array $pivotData = [])
    {
    }

    public function unlink(ActiveRecord $record)
    {
    }

    /**
     * Unlink every associated record, method will return amount of affected rows.
     *
     * @return int
     */
    public function unlinkAll()
    {
        $innerKey = $this->definition[ActiveRecord::INNER_KEY];

        return $this->pivotTable()->delete([
            $this->definition[ActiveRecord::THOUGHT_INNER_KEY] => $this->parent->getField($innerKey)
        ])->run();
    }
}