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

    protected function createSelector()
    {
        return new Selector($this->definition[static::RELATION_TYPE], $this->orm);
    }

    abstract public function getContent();

    abstract protected function loadData();
}
