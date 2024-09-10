<?php

declare(strict_types=1);

namespace Spiral\Prototype\NodeVisitors;

use PhpParser\BuilderHelpers;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeVisitorAbstract;
use Spiral\Prototype\Utils;

/**
 * Ensure correct placement and presence of __construct.
 */
final class DefineConstructor extends NodeVisitorAbstract
{
    public function leaveNode(Node $node): ?Node
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
        /**
         * @psalm-suppress InternalMethod
         * @psalm-suppress InternalClass
         */
        $constructor->flags = BuilderHelpers::addModifier(
            $constructor->flags,
            Class_::MODIFIER_PUBLIC
        );

        return $constructor;
    }
}
