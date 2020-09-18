<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Prototype\NodeVisitors\ClassNode;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

final class DeclareClass extends NodeVisitorAbstract
{
    private $namespace;
    private $class;

    /**
     * @param Node $node
     * @return int|null|Node|Node[]
     */
    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Namespace_) {
            $this->namespace = join('\\', $node->name->parts);
        }

        if ($node instanceof Node\Stmt\Class_) {
            $this->class = $node->name->name;

            return NodeTraverser::STOP_TRAVERSAL;
        }

        return null;
    }

    /**
     * @return string|null
     */
    public function getNamespace(): ?string
    {
        return $this->namespace;
    }

    /**
     * @return string|null
     */
    public function getClass(): ?string
    {
        return $this->class;
    }
}
