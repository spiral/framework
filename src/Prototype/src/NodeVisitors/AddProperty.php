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
use PhpParser\Builder\Property;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use Spiral\Prototype\ClassNode;
use Spiral\Prototype\Dependency;
use Spiral\Prototype\Utils;

final class AddProperty extends NodeVisitorAbstract
{
    private ClassNode $definition;
    private bool $useTypedProperties;
    private bool $noPhpDoc;

    public function __construct(ClassNode $definition, bool $useTypedProperties = false, bool $noPhpDoc = false)
    {
        $this->definition = $definition;
        $this->useTypedProperties = $useTypedProperties;
        $this->noPhpDoc = $noPhpDoc;
    }

    /**
     * @return Node\Stmt\Class_|null
     */
    public function leaveNode(Node $node)
    {
        if (!$node instanceof Class_) {
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

    private function definePlacementID(Class_ $node): int
    {
        foreach ($node->stmts as $index => $child) {
            if ($child instanceof ClassMethod || $child instanceof Node\Stmt\Property) {
                return $index;
            }
        }

        return 0;
    }

    private function buildProperty(Dependency $dependency): Node\Stmt\Property
    {
        $b = new Property($dependency->property);
        $b->makePrivate();

        if ($this->useTypedProperty()) {
            $b->setType($this->getPropertyType($dependency));
        }

        if ($this->renderDoc()) {
            $b->setDocComment(new Doc(sprintf('/** @var %s */', $this->getPropertyType($dependency))));
        }

        return $b->getNode();
    }

    private function useTypedProperty(): bool
    {
        return $this->useTypedProperties && method_exists(Property::class, 'setType');
    }

    private function renderDoc(): bool
    {
        return !($this->useTypedProperties && $this->noPhpDoc);
    }

    private function getPropertyType(Dependency $dependency): string
    {
        foreach ($this->definition->getStmts() as $stmt) {
            if (($stmt->name === $dependency->type->fullName) && $stmt->alias) {
                return $stmt->alias;
            }
        }

        return $dependency->type->getAliasOrShortName();
    }
}
