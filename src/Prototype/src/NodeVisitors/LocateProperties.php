<?php

declare(strict_types=1);

namespace Spiral\Prototype\NodeVisitors;

use PhpParser\Modifiers;
use PhpParser\Node;
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
            $node instanceof Node\Expr\PropertyFetch &&
            $node->var instanceof Node\Expr\Variable &&
            $node->var->name === 'this' &&
            $node->name instanceof Node\Identifier
        ) {
            $this->requested[$node->name->name] = $node->name->name;
        }

        if ($node instanceof Node\Stmt\Property) {
            foreach ($node->props as $prop) {
                if ($prop instanceof Node\Stmt\PropertyProperty) {
                    $this->properties[$prop->name->name] = $prop->name->name;
                }
            }
        }

        if ($this->isPromotedProperty($node)) {
            $this->properties[$node->var->name] = $node->var->name;
        }
    }

    private function isPromotedProperty(Node $node): bool
    {
        if (!$node instanceof Node\Param) {
            return false;
        }

        return $node->flags === Modifiers::PUBLIC
            || $node->flags === Modifiers::PROTECTED
            || $node->flags === Modifiers::PRIVATE;
    }
}
