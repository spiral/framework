<?php

declare(strict_types=1);

namespace Spiral\Prototype\NodeVisitors;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use PhpParser\NodeVisitorAbstract;

/**
 * Locate all declared and requested properties.
 */
final class LocateProperties extends NodeVisitorAbstract
{
    /** @var array<non-empty-string, non-empty-string> */
    private array $properties = [];
    /** @var array<non-empty-string, non-empty-string> */
    private array $requested = [];

    /**
     * Get names of all virtual properties.
     */
    public function getProperties(): array
    {
        return \array_values(\array_diff(
            \array_values($this->requested),
            \array_values($this->properties)
        ));
    }

    /**
     * Detected declared and requested nodes.
     */
    public function enterNode(Node $node): void
    {
        if (
            $node instanceof PropertyFetch &&
            $node->var instanceof Variable &&
            $node->var->name === 'this' &&
            $node->name instanceof Identifier
        ) {
            $this->requested[$node->name->name] = $node->name->name;
        }

        if ($node instanceof Property) {
            foreach ($node->props as $prop) {
                if ($prop instanceof PropertyProperty) {
                    $this->properties[$prop->name->name] = $prop->name->name;
                }
            }
        }

        $this->promotedProperties($node);
    }

    private function promotedProperties(Node $node): void
    {
        if (!$node instanceof Param || !$node->var instanceof Variable) {
            return;
        }

        if (
            $node->flags & Class_::MODIFIER_PUBLIC ||
            $node->flags & Class_::MODIFIER_PROTECTED ||
            $node->flags & Class_::MODIFIER_PRIVATE
        ) {
            $this->properties[$node->var->name] = $node->var->name;
        }
    }
}
