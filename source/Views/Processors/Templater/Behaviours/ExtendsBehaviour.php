<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\View\Compiler\Processors\Templater\Behaviours;

use Spiral\Components\View\Compiler\Processors\TemplateProcessor;
use Spiral\Components\View\Compiler\Processors\Templater\BehaviourInterface;
use Spiral\Components\View\Compiler\Processors\Templater\ImporterInterface;
use Spiral\Components\View\Compiler\Processors\Templater\Node;

class ExtendsBehaviour implements BehaviourInterface
{
    /**
     * Parent (extended) node, treat it as page or element layout.
     *
     * @var Node
     */
    protected $parent = null;

    /**
     * Attributes defined using extends tag.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * Extends behaviour used to send command to Node that all blocks should replace parent blocks
     * or create set of outer blocks.
     *
     * @param Node  $parent
     * @param array $attributes
     */
    public function __construct(Node $parent, array $attributes)
    {
        $this->parent = $parent;
        $this->attributes = $attributes;
    }

    /**
     * Get parent Node (layout) to be extended.
     *
     * @return Node
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Every import defined on parent level.
     *
     * @return ImporterInterface[]
     */
    public function getImporters()
    {
        /**
         * @var TemplateProcessor $templater
         */
        $templater = $this->parent->getSupervisor();

        return $templater->getImporters();
    }

    /**
     * Get all blocks defined using extend tag (tag attributes).
     *
     * @return array
     */
    public function getBlocks()
    {
        return $this->attributes;
    }
}