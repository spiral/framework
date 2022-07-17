<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Prototype\NodeVisitors;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\BuilderHelpers;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use Spiral\Prototype\Utils;

/**
 * Ensure correct placement and presence of __construct.
 */
final class DefineConstructor extends NodeVisitorAbstract
{
    /**
     * @return int|null|Node|Node[]
     */
    public function leaveNode(Node $node)
    {
        if (!$node instanceof Class_) {
            return null;
        }

        $placementID = 0;
        foreach ($node->stmts as $index => $child) {
            $placementID = $index;
            if ($child instanceof ClassMethod) {
                if ($child->name->name === '__construct') {
                    $node->setAttribute('constructor', $child);

                    return null;
                }

                break;
            }
        }

        $constructor = $this->buildConstructor();
        $node->setAttribute('constructor', $constructor);
        $node->stmts = Utils::injectValues($node->stmts, $placementID, [$constructor]);

        return $node;
    }

    private function buildConstructor(): ClassMethod
    {
        $constructor = new ClassMethod('__construct');
        $constructor->flags = BuilderHelpers::addModifier(
            $constructor->flags,
            Class_::MODIFIER_PUBLIC
        );

        return $constructor;
    }
}
