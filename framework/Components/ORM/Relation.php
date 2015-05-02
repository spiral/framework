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
    const OUTER_TABLE = 1;

    const RELATION_TYPE = Entity::HAS_ONE;

    /**
     * Relation name. Name will be used to create automatic table alias while enable relation inloading.
     *
     * @var string
     */
    protected $name = '';

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

    public function __construct($name, array $definition, Entity $parent = null, $data = array())
    {
        $this->name = $name;
        $this->definition = $definition;
        $this->target = $definition[static::RELATION_TYPE];
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
}