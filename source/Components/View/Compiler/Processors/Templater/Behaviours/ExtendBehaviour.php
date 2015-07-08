<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\View\Compiler\Processors\Templater\Behaviours;

use Spiral\Components\View\Compiler\Processors\Templater\BehaviourInterface;
use Spiral\Components\View\Compiler\Processors\Templater\Node;

class ExtendBehaviour implements BehaviourInterface
{
    protected $parent = null;

    protected $attributes = [];

    public function __construct(Node $parent, array $attributes)
    {
        $this->parent = $parent;
        $this->attributes = $attributes;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }
}