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

final class LocateVariables extends NodeVisitorAbstract
{
    /** @var array */
    private $vars = [];

    /**
     * @inheritDoc
     */
    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Class_) {
            foreach ($node->stmts as $stmt) {
                if ($stmt instanceof Node\Stmt\ClassMethod && $stmt->name === '__construct') {
                    return $stmt;
                }
            }
            return NodeTraverser::DONT_TRAVERSE_CHILDREN;
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
