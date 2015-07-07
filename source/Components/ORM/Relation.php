<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ORM;

abstract class Relation implements RelationInterface
{
    const NO_DATA = -1;

    /**
     * Relation type.
     */
    const RELATION_TYPE = ActiveRecord::HAS_ONE;

    const MULTIPLE = false;

    /**
     * ORM component.
     *
     * @invisible
     * @var ORM
     */
    protected $orm = null;

    protected $parent = null;

    /**
     * Relation definition fetched from ORM schema.
     *
     * @var array
     */
    protected $definition = [];

    protected $data = [];

    public function __construct(ORM $orm, ActiveRecord $parent, array $definition, $data = null)
    {
        $this->orm = $orm;
        $this->parent = $parent;
        $this->definition = $definition;
        $this->data = $data;
    }

    protected function getClass()
    {
        return $this->definition[static::RELATION_TYPE];
    }

    protected function createSelector()
    {
        return new Selector($this->definition[static::RELATION_TYPE], $this->orm);
    }

    public function getContent()
    {
        if (is_object($this->data))
        {
            return $this->data;
        }

        if (empty($this->data) && empty($this->loadData()))
        {
            //Can not be loaded
            return static::MULTIPLE ? [] : null;
        }

        if (static::MULTIPLE)
        {
            return $this->data = new ModelIterator($this->orm, $this->getClass(), $this->data);
        }

        return $this->data = $this->orm->construct($this->getClass(), $this->data);
    }

    protected function loadData()
    {
        if (!$this->parent->isLoaded())
        {
            return null;
        }

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
}
