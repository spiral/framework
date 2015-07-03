<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ORM;

abstract class Relation implements RelationInerface
{
    /**
     * Internal relation type used to resolve outer table name.
     */
    const OUTER_TABLE = 1;

    /**
     * Relation type.
     */
    const RELATION_TYPE = ActiveRecord::HAS_ONE;

    protected $orm = null;

    protected $parent = null;

    /**
     * Relation definition fetched from ORM schema.
     *
     * @var array
     */
    protected $definition = [];

    /**
     * Target model to be loaded.
     *
     * @var string
     */
    protected $target = '';

    protected $data = [];

    public function __construct(ORM $orm, ActiveRecord $parent = null, array $definition, $data = null)
    {
        $this->orm = $orm;
        $this->parent = $parent;
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
        //database is not nesessary right
        $selector = new Selector(!empty($orm) ? $orm : ORM::getInstance(), $this->getTarget());

        return $selector;
    }

    /**
     * @return mixed
     */
    abstract public function getContent();
}
