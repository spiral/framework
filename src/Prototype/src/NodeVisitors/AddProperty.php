<?php

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
    public function __construct(
        private readonly ClassNode $definition,
        private readonly bool $useTypedProperties = false,
        private readonly bool $noPhpDoc = false
    ) {
    }

    public function leaveNode(Node $node): ?Node
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

    private function definePlacementID(Node\Stmt\Class_ $node): int
    {
        foreach ($node->stmts as $index => $child) {
            if ($child instanceof Node\Stmt\ClassMethod || $child instanceof Node\Stmt\Property) {
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
            $b->setDocComment(new Doc(\sprintf('/** @var %s */', $this->getPropertyType($dependency))));
        }

        return $b->getNode();
    }

    private function useTypedProperty(): bool
    {
        return $this->useTypedProperties && \method_exists(Property::class, 'setType');
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
