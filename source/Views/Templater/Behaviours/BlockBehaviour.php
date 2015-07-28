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

class BlockBehaviour implements BehaviourInterface
{
    /**
     * Block name.
     *
     * @var string
     */
    protected $name = '';

    /**
     * Block behaviour used to command to Node that html token defines sub node (block).
     *
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Block name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}