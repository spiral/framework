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
    const RELATION_TYPE  = Entity::HAS_ONE;
    const DEFAULT_LOADER = Selector::POSTLOAD;

    protected $definition = array();

    protected $target = null;

    public function __construct(array $definition, Entity $parent = null, $data = array())
    {
        $this->definition = $definition;
        $this->target = $definition[static::RELATION_TYPE];
    }

    public function getTarget()
    {
        return $this->target;
    }

    public function inload($parentTable, Selector $selector, ORM $orm)
    {
    }
}