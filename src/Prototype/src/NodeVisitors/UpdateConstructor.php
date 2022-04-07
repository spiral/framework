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
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\StaticCall;
use Spiral\Prototype\Annotation\Parser;
use Spiral\Prototype\Annotation\Line;
use Spiral\Prototype\ClassNode\ConstructorParam;
use PhpParser\Builder\Param;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use Spiral\Prototype\Annotation;
use Spiral\Prototype\ClassNode;
use Spiral\Prototype\Dependency;
use Spiral\Prototype\Utils;

/**
 * Injects new constructor dependencies and modifies comment.
 */
final class UpdateConstructor extends NodeVisitorAbstract
{
    private ClassNode $definition;

    public function __construct(ClassNode $definition)
    {
        $this->definition = $definition;
    }

    /**
     * @return int|null|Node|Node[]
     */
    public function leaveNode(Node $node)
    {
        if (!$node instanceof Class_) {
            return null;
        }

        $constructor = $this->getConstructorAttribute($node);
        $this->addDependencies($constructor);
        if (!$this->definition->hasConstructor && $this->definition->constructorParams) {
            $this->addParentConstructorCall($constructor);
        }

        $constructor->setDocComment(
            $this->addComments($constructor->getDocComment())
        );

        return $node;
    }

    /**
     * Add dependencies to constructor method.
     */
    private function addDependencies(ClassMethod $constructor): void
    {
        foreach ($this->definition->dependencies as $dependency) {
            array_unshift($constructor->params, $this->buildConstructorParam($dependency));
            array_unshift(
                $constructor->stmts,
                new Expression(
                    new Assign(
                        new PropertyFetch(new Variable('this'), $dependency->property),
                        new Variable($dependency->var)
                    )
                )
            );
        }
    }

    private function buildConstructorParam(Dependency $dependency): Node
    {
        $param = new Param($dependency->var);
        return $param->setType(new Name($this->getPropertyType($dependency)))->getNode();
    }

    private function addParentConstructorCall(ClassMethod $constructor): void
    {
        $parentConstructorDependencies = [];
        foreach ($this->definition->constructorParams as $param) {
            $parentConstructorDependencies[] = new Arg(new Variable($param->name));

            $cp = new Param($param->name);
            if (!empty($param->type)) {
                $type = $this->getParamType($param);
                if ($param->nullable) {
                    $type = "?$type";
                }

                $cp->setType(new Name($type));
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

        if ($parentConstructorDependencies) {
            array_unshift(
                $constructor->stmts,
                new Expression(
                    new StaticCall(
                        new Name('parent'),
                        '__construct',
                        $parentConstructorDependencies
                    )
                )
            );
        }
    }

    private function getConstructorAttribute(Class_ $node): ClassMethod
    {
        return $node->getAttribute('constructor');
    }

    /**
     * Add PHPDoc comments into __construct.
     *
     * @param Doc|null $doc
     */
    private function addComments(Doc $doc = null): Doc
    {
        $an = new Parser($doc ? $doc->getText() : '');

        $params = [];

        foreach ($this->definition->dependencies as $dependency) {
            $params[] = new Line(
                sprintf('%s $%s', $this->getPropertyType($dependency), $dependency->var),
                'param'
            );
        }

        if (!$this->definition->hasConstructor) {
            foreach ($this->definition->constructorParams as $param) {
                if (!empty($param->type)) {
                    $type = $this->getParamType($param);
                    if ($param->nullable) {
                        $type = "$type|null";
                    }

                    $params[] = new Line(
                        sprintf($param->isVariadic ? '%s ...$%s' : '%s $%s', $type, $param->name),
                        'param'
                    );
                } else {
                    $params[] = new Line(
                        sprintf('$%s', $param->name),
                        'param'
                    );
                }
            }
        }

        $placementID = 0;
        $previous = null;
        foreach ($an->lines as $index => $line) {
            // always next node
            $placementID = $index + 1;

            // inject before this parameters
            if ($line->is(['throws', 'return'])) {
                // insert before given node
                $placementID--;
                break;
            }

            $previous = $line;
        }

        if ($previous !== null && !$previous->isEmpty()) {
            $placementID++;
        }

        $an->lines = Utils::injectValues($an->lines, $placementID, $params);

        return new Doc($an->compile());
    }

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

    private function getParamType(ConstructorParam $param): string
    {
        foreach ($this->definition->getStmts() as $stmt) {
            if ($stmt->name === $param->type->fullName) {
                if ($stmt->alias) {
                    return $stmt->alias;
                }
            }
        }

        if ($param->type->alias) {
            return $param->type->alias;
        }

        return $param->type->getSlashedShortName($param->isBuiltIn());
    }
}
