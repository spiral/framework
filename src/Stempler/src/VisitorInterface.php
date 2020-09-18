<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Stempler;

use Spiral\Stempler\Node\NodeInterface;

interface VisitorInterface
{
    /**
     * If NodeVisitor::enterNode() returns DONT_TRAVERSE_CHILDREN, child nodes
     * of the current node will not be traversed for any visitors.
     *
     * For subsequent visitors enterNode() will still be called on the current
     * node and leaveNode() will also be invoked for the current node.
     */
    public const DONT_TRAVERSE_CHILDREN = 1;

    /**
     * If NodeVisitor::enterNode() or NodeVisitor::leaveNode() returns
     * STOP_TRAVERSAL, traversal is aborted.
     *
     * The afterTraverse() method will still be invoked.
     */
    public const STOP_TRAVERSAL = 2;

    /**
     * If NodeVisitor::leaveNode() returns REMOVE_NODE for a node that occurs
     * in an array, it will be removed from the array.
     *
     * For subsequent visitors leaveNode() will still be invoked for the
     * removed node.
     */
    public const REMOVE_NODE = 3;

    /**
     * If NodeVisitor::enterNode() returns DONT_TRAVERSE_CURRENT_AND_CHILDREN, child nodes
     * of the current node will not be traversed for any visitors.
     *
     * For subsequent visitors enterNode() will not be called as well.
     * leaveNode() will be invoked for visitors that has enterNode() method invoked.
     */
    public const DONT_TRAVERSE_CURRENT_AND_CHILDREN = 4;

    /**
     * @param NodeInterface|mixed $node
     * @param VisitorContext      $ctx
     * @return mixed
     */
    public function enterNode($node, VisitorContext $ctx);

    /**
     * @param NodeInterface|mixed $node
     * @param VisitorContext      $ctx
     * @return mixed
     */
    public function leaveNode($node, VisitorContext $ctx);
}
