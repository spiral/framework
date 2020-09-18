<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Prototype\NodeVisitors;

use PhpParser\Builder\Property;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use Spiral\Prototype\ClassNode;
use Spiral\Prototype\Dependency;
use Spiral\Prototype\Utils;

final class AddProperty extends NodeVisitorAbstract
{
    /** @var ClassNode */
    private $definition;

    /**
     * @param ClassNode $definition
     */
    public function __construct(ClassNode $definition)
    {
        $this->definition = $definition;
    }

    /**
     * @param Node $node
     * @return int|null|Node|Node[]
     */
    public function leaveNode(Node $node)
    {
        if (!$node instanceof Node\Stmt\Class_) {
            return null;
        }

        $nodes = [];
        foreach ($this->definition->dependencies as $dependency) {
            $nodes[] = $this->buildProperty($dependency);
        }

        $placementID = $this->definePlacementID($node);
        $node->stmts = Utils::injectValues($node->stmts, $placementID, $nodes);

        return $node;
    }

    /**
     * @param Node\Stmt\Class_ $node
     * @return int
     */
    private function definePlacementID(Node\Stmt\Class_ $node): int
    {
        foreach ($node->stmts as $index => $child) {
            if ($child instanceof Node\Stmt\ClassMethod || $child instanceof Node\Stmt\Property) {
                return $index;
            }
        }

        return 0;
    }

    /**
     * @param Dependency $dependency
     * @return Node\Stmt\Property
     */
    private function buildProperty(Dependency $dependency): Node\Stmt\Property
    {
        $b = new Property($dependency->property);
        $b->makePrivate();
        $b->setDocComment(new Doc(sprintf('/** @var %s */', $this->getPropertyType($dependency))));

        return $b->getNode();
    }

    /**
     * @param Dependency $dependency
     * @return string
     */
    private function getPropertyType(Dependency $dependency): string
    {
        foreach ($this->definition->getStmts() as $stmt) {
            if ($stmt->name === $dependency->type->fullName) {
                if ($stmt->alias) {
                    return $stmt->alias;
                }
            }
        }

        return $dependency->type->getAliasOrShortName();
    }
}
