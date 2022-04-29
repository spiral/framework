<?php

declare(strict_types=1);

namespace Spiral\Prototype\NodeVisitors\ClassNode;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

final class DeclareClass extends NodeVisitorAbstract
{
    private ?string $namespace = null;
    private ?string $class = null;

    public function enterNode(Node $node): ?int
    {
        if ($node instanceof Node\Stmt\Namespace_) {
            $this->namespace = \implode('\\', $node->name->parts);
        }

        if ($node instanceof Node\Stmt\Class_) {
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
