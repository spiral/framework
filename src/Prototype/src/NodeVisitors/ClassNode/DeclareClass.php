<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Prototype\NodeVisitors\ClassNode;

use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

final class DeclareClass extends NodeVisitorAbstract
{
    private $namespace;
    private $class;

    /**
     * @return int|null|Node|Node[]
     */
    public function enterNode(Node $node)
    {
        if ($node instanceof Namespace_) {
            $this->namespace = implode('\\', $node->name->parts);
        }

        if ($node instanceof Class_) {
            $this->class = $node->name->name;

            return NodeTraverser::STOP_TRAVERSAL;
        }

        return null;
    }

    public function getNamespace(): ?string
    {
        return $this->namespace;
    }

    public function getClass(): ?string
    {
        return $this->class;
    }
}
