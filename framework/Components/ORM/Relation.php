<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ORM;

abstract class Relation
{
    /**
     * Internal relation type used to resolve outer table name.
     */
    const OUTER_TABLE = 1;

    /**
     * Relation type.
     */
    const RELATION_TYPE = ActiveRecord::HAS_ONE;

    /**
     * Relation name. Name will be used to create automatic table alias while enable relation inloading.
     *
     * @var string
     */
    protected $name = '';

    protected $parent = null;

    /**
     * Relation definition fetched from ORM schema.
     *
     * @var array
     */
    protected $definition = array();

    /**
     * Target model to be loaded.
     *
     * @var string
     */
    protected $target = '';

    protected $data = array();

    public function __construct($name, array $definition, ActiveRecord $parent = null, $data = null)
    {
        $this->parent = $parent;
        $this->name = $name;
        $this->definition = $definition;
        $this->target = $definition[static::RELATION_TYPE];
        $this->data = $data;
    }

    /**
     * Get relation target model class name.
     *
     * @return string
     */
    public function getTarget()
    {
        return $this->target;
    }

    protected function loadData()
    {
        $selector = $this->getSelector();

        dump($selector);
    }

    public function getSelector()
    {
        $orm = ORM::getInstance();

        $selector = Selector::make(array(
            'schema'   => $orm->getSchema($this->getTarget()),
            'database' => $this->parent->dbalDatabase(),
            'orm'      => $orm
        ));

        return $selector;
    }

    /**
     * @return mixed
     */
    abstract public function getContent();
}
