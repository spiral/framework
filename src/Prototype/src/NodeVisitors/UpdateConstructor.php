<?php

declare(strict_types=1);

namespace Spiral\Prototype\NodeVisitors;

use PhpParser\Builder\Param;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use Spiral\Prototype\ClassNode;
use Spiral\Prototype\Dependency;

/**
 * Injects new constructor dependencies and modifies comment.
 */
final class UpdateConstructor extends NodeVisitorAbstract
{
    public function __construct(
        private readonly ClassNode $definition
    ) {
    }

    public function leaveNode(Node $node): int|Node|null
    {
        if (!$node instanceof Node\Stmt\Class_) {
            return null;
        }

        $constructor = $this->getConstructorAttribute($node);
        $this->addDependencies($constructor);
        if (!$this->definition->hasConstructor && $this->definition->constructorParams) {
            $this->addParentConstructorCall($constructor);
        }

        return $node;
    }

    /**
     * Add dependencies to constructor method.
     */
    private function addDependencies(Node\Stmt\ClassMethod $constructor): void
    {
        foreach ($this->definition->dependencies as $dependency) {
            \array_unshift($constructor->params, $this->buildConstructorParam($dependency));
        }
    }

    private function buildConstructorParam(Dependency $dependency): Node
    {
        $param = new Param($dependency->var);
        $param->makePrivate();
        $param->makeReadonly();

        return $param->setType(new Node\Name($this->getPropertyType($dependency)))->getNode();
    }

    private function addParentConstructorCall(Node\Stmt\ClassMethod $constructor): void
    {
        $parentConstructorDependencies = [];
        foreach ($this->definition->constructorParams as $param) {
            $parentConstructorDependencies[] = new Node\Arg(new Node\Expr\Variable($param->name));

            $cp = new Param($param->name);
            if (!empty($param->type)) {
                $type = $this->getParamType($param);
                if ($param->nullable) {
                    $type = \sprintf('?%s', $type);
                }

                $cp->setType(new Node\Name($type));
            }

            if ($param->byRef) {
                $cp->makeByRef();
            }

            if ($param->isVariadic) {
                $cp->makeVariadic();
            }

            if ($param->hasDefault) {
                $cp->setDefault($param->default);
            }
            $constructor->params[] = $cp->getNode();
        }

        if ($parentConstructorDependencies !== []) {
            \array_unshift(
                $constructor->stmts,
                new Node\Stmt\Expression(
                    new Node\Expr\StaticCall(
                        new Node\Name('parent'),
                        '__construct',
                        $parentConstructorDependencies
                    )
                )
            );
        }
    }

    private function getConstructorAttribute(Node\Stmt\Class_ $node): Node\Stmt\ClassMethod
    {
        return $node->getAttribute('constructor');
    }

    private function getPropertyType(Dependency $dependency): string
    {
        foreach ($this->definition->getStmts() as $stmt) {
            if ($stmt->name === $dependency->type->fullName && $stmt->alias) {
                return $stmt->alias;
            }
        }

        return $dependency->type->getAliasOrShortName();
    }

    private function getParamType(ClassNode\ConstructorParam $param): string
    {
        foreach ($this->definition->getStmts() as $stmt) {
            if ($stmt->name === $param->type->fullName && $stmt->alias) {
                return $stmt->alias;
            }
        }

        if ($param->type->alias) {
            return $param->type->alias;
        }

        return $param->type->getSlashedShortName($param->isBuiltIn());
    }
}
