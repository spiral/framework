<?php

declare(strict_types=1);

namespace Spiral\Prototype\NodeVisitors\ClassNode;

use PhpParser\Node;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;

final class LocateVariables extends NodeVisitorAbstract
{
    private array $vars = [];

    public function enterNode(Node $node): int|Node\Stmt\ClassMethod|null
    {
        if ($node instanceof Node\Stmt\Class_) {
            foreach ($node->stmts as $stmt) {
                if ($stmt instanceof Node\Stmt\ClassMethod && $stmt->name->toLowerString() === '__construct') {
                    return $stmt;
                }
            }

            return NodeVisitor::DONT_TRAVERSE_CHILDREN;
        }

        if ($node instanceof Node\Expr\Variable) {
            $this->vars[] = $node->name;
        }

        return null;
    }

    public function getVars(): array
    {
        return $this->vars;
    }
}
