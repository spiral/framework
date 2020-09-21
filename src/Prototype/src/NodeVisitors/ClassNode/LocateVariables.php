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
use PhpParser\NodeVisitorAbstract;

final class LocateVariables extends NodeVisitorAbstract
{
    /** @var array */
    private $vars = [];

    /**
     * @param Node $node
     * @return int|Node|Node[]|void|null
     */
    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Expr\Variable) {
            $this->vars[] = $node->name;
        }
    }

    /**
     * @return array
     */
    public function getVars(): array
    {
        return $this->vars;
    }
}
