<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Stempler\Behaviours;

use Spiral\Stempler\BehaviourInterface;
use Spiral\Stempler\HtmlTokenizer;
use Spiral\Stempler\ImporterInterface;
use Spiral\Stempler\Node;
use Spiral\Stempler\Supervisor;

/**
 * Points node to it's parent.
 */
class ExtendsBehaviour implements BehaviourInterface
{
    /**
     * Parent (extended) node, treat it as page or element layout.
     *
     * @var Node
     */
    private $parent = null;

    /**
     * Attributes defined using extends tag.
     *
     * @var array
     */
    private $attributes = [];

    /**
     * @var array
     */
    private $token = [];

    /**
     * @param Node  $parent
     * @param array $token
     */
    public function __construct(Node $parent, array $token)
    {
        $this->parent = $parent;
        $this->token = $token;
        $this->attributes = $token[HtmlTokenizer::TOKEN_ATTRIBUTES];
    }

    /**
     * Node which are getting extended.
     *
     * @return Node
     */
    public function extendedNode()
    {
        return $this->parent;
    }

    /**
     * Every import defined in parent (extended node).
     *
     * @return ImporterInterface[]
     */
    public function parentImports()
    {
        $supervisor = $this->parent->supervisor();
        if (!$supervisor instanceof Supervisor) {
            return [];
        }

        return $supervisor->getImporters();
    }

    /**
     * Set of blocks defined at moment of extend definition.
     *
     * @return array
     */
    public function dynamicBlocks()
    {
        return $this->attributes;
    }
}