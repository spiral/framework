<?php

declare(strict_types=1);

namespace Spiral\Prototype\NodeVisitors;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use Spiral\Prototype\Traits\PrototypeTrait;

/**
 * Remove PrototypeTrait use.
 */
final class RemoveUse extends NodeVisitorAbstract
{
    public function leaveNode(Node $node): int|Node|null
    {
        if (!$node instanceof Node\Stmt\Use_) {
            return null;
        }

        foreach ($node->uses as $index => $use) {
            if ($use->name->toString() === PrototypeTrait::class) {
                unset($node->uses[$index]);
            }
        }

        if (empty($node->uses)) {
            return NodeTraverser::REMOVE_NODE;
        }

        return $node;
    }
}
