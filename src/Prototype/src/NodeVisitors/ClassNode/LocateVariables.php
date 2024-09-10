<?php

declare(strict_types=1);

namespace Spiral\Prototype\NodeVisitors\ClassNode;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

final class LocateVariables extends NodeVisitorAbstract
{
    private array $vars = [];

    public function enterNode(Node $node): int|ClassMethod|null
    {
        if ($node instanceof Class_) {
            foreach ($node->stmts as $stmt) {
                if ($stmt instanceof ClassMethod && $stmt->name === '__construct') {
                    return $stmt;
                }
            }
            return NodeTraverser::DONT_TRAVERSE_CHILDREN;
        }

        if ($node instanceof Variable) {
            $this->vars[] = $node->name;
        }

        return null;
    }

    public function getVars(): array
    {
        return $this->vars;
    }
}
