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
use Spiral\Components\ORM\ORM;
use Spiral\Components\ORM\ORMException;
use Spiral\Components\ORM\RelationInterface;

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
     * Reset relation pre-loaded data.
     *
     * @param array $data
     */
    public function reset(array $data = [])
    {
        foreach ($this->relations as $relation)
        {
            $relation->reset($data, false);
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
     * @return static
     */
    public function getData()
    {
        return $this;
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
        throw new ORMException("Unable to set data for morphed relation.");
    }

    /**
     * ActiveRecord may ask relation data to be saved, save content will work ONLY for pre-loaded
     * relation content. This method better not be called outside of active record.
     *
     * @param bool $validate
     * @return bool
     */
    public function saveData($validate = true)
    {
        foreach ($this->relations as $relation)
        {
            if (!$relation->saveData($validate))
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

    protected function getRelation($alias)
    {
        if (isset($this->relations[$alias]))
        {
            return $this->relations[$alias];
        }

        if (!isset($this->definition[ActiveRecord::MORPHED_ALIASES][$alias]))
        {
            throw new ORMException("No such sub-relation '{$alias}'.");
        }

        //We have to create custom defintition
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

    public function __get($alias)
    {
        return $this->getRelation($alias)->getData();
    }

    public function __call($alias, array $arguments)
    {
        //???
        return $this->getRelation($alias);
    }

    //TODO: count method
    public function count()
    {
        return mt_rand(0, 10000);
    }

    public function link()
    {
    }

    public function unlink()
    {
    }
}