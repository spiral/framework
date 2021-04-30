<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Attributes\Internal\FallbackAttributeReader;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

/**
 * @internal AttributeFinderVisitor is an internal library class, please do not use it in your code.
 * @psalm-internal Spiral\Attributes
 *
 * @psalm-type AttributePrototypeList = array<AttributePrototype>
 *
 * @psalm-type FunctionEndLine = positive-int
 *
 * @psalm-type ClassName = string
 * @psalm-type ConstantName = string
 * @psalm-type PropertyName = string
 * @psalm-type ParameterName = string
 */
final class AttributeFinderVisitor extends NodeVisitorAbstract
{
    /**
     * @var array
     */
    private $context = [
        AttributeParser::CTX_FUNCTION  => '',
        AttributeParser::CTX_NAMESPACE => '',
        AttributeParser::CTX_CLASS     => '',
        AttributeParser::CTX_TRAIT     => '',
    ];

    /**
     * @var array<ClassName, AttributePrototypeList>
     */
    private $classes = [];

    /**
     * @var array<FunctionEndLine, AttributePrototypeList>
     */
    private $functions = [];

    /**
     * @var array<ClassName, array<ConstantName, AttributePrototypeList>>
     */
    private $constants = [];

    /**
     * @var array<ClassName, array<PropertyName, AttributePrototypeList>>
     */
    private $properties = [];

    /**
     * @var array<FunctionEndLine, array<ParameterName, AttributePrototypeList>>
     */
    private $parameters = [];

    /**
     * @var string
     */
    private $file;

    /**
     * @var AttributeParser
     */
    private $parser;

    /**
     * @param string $file
     * @param AttributeParser $parser
     */
    public function __construct(string $file, AttributeParser $parser)
    {
        $this->file = $file;
        $this->parser = $parser;
    }

    /**
     * @return array
     */
    public function __debugInfo(): array
    {
        return [
            'classes'    => $this->classes,
            'functions'  => $this->functions,
            'constants'  => $this->constants,
            'properties' => $this->properties,
            'parameters' => $this->parameters,
        ];
    }

    /**
     * @return array<ClassName, AttributePrototypeList>
     */
    public function getClasses(): array
    {
        return $this->classes;
    }

    /**
     * @return array<FunctionEndLine, AttributePrototypeList>
     */
    public function getFunctions(): array
    {
        return $this->functions;
    }

    /**
     * @return array<ClassName, array<ConstantName, AttributePrototypeList>>
     */
    public function getConstants(): array
    {
        return $this->constants;
    }

    /**
     * @return array<ClassName, array<PropertyName, AttributePrototypeList>>
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @return array<FunctionEndLine, array<ParameterName, AttributePrototypeList>>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param Node $node
     * @return int|null
     * @throws \Throwable
     */
    public function enterNode(Node $node): ?int
    {
        $this->updateContext($node);

        if ($node instanceof Node\Stmt\ClassLike) {
            foreach ($this->parse($node->attrGroups) as $prototype) {
                $this->classes[$node->namespacedName->toString()][] = $prototype;
            }

            return null;
        }

        if ($node instanceof Node\FunctionLike) {
            $line = $node->getEndLine();

            foreach ($this->parse($node->getAttrGroups()) as $prototype) {
                $this->functions[$line][] = $prototype;
            }

            foreach ($node->getParams() as $param) {
                foreach ($this->parse($param->attrGroups) as $prototype) {
                    assert(\is_string($param->var->name), 'Function parameter name should be an identifier');

                    $this->parameters[$line][$param->var->name][] = $prototype;
                }
            }

            return NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
        }

        if ($node instanceof Node\Stmt\ClassConst) {
            $class = $this->fqn();

            foreach ($this->parse($node->attrGroups) as $prototype) {
                foreach ($node->consts as $const) {
                    $this->constants[$class][$this->name($const->name)][] = $prototype;
                }
            }

            return NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
        }

        if ($node instanceof Property) {
            $class = $this->fqn();

            foreach ($this->parse($node->attrGroups) as $prototype) {
                foreach ($node->props as $property) {
                    $this->properties[$class][$this->name($property->name)][] = $prototype;
                }
            }

            return NodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
        }

        return null;
    }

    /**
     * @param Node $node
     */
    public function leaveNode(Node $node): void
    {
        if ($node instanceof Node\Stmt\Namespace_) {
            $this->context[AttributeParser::CTX_NAMESPACE] = '';

            return;
        }

        if ($node instanceof Node\Stmt\ClassLike) {
            $this->context[AttributeParser::CTX_CLASS] = '';
            $this->context[AttributeParser::CTX_TRAIT] = '';

            return;
        }

        if ($node instanceof Node\FunctionLike) {
            $this->context[AttributeParser::CTX_FUNCTION] = '';

            return;
        }
    }

    /**
     * @param Node $node
     */
    private function updateContext(Node $node): void
    {
        switch (true) {
            case $node instanceof Node\Stmt\Namespace_:
                $this->context[AttributeParser::CTX_NAMESPACE] = $this->name($node->name);
                break;

            case $node instanceof Node\Stmt\ClassLike:
                $this->context[AttributeParser::CTX_CLASS] = $this->name($node->name);

            // no break
            case $node instanceof Node\Stmt\Trait_:
                $this->context[AttributeParser::CTX_TRAIT] = $this->name($node->name);
                break;

            case $node instanceof Node\Stmt\Function_:
            case $node instanceof Node\Stmt\ClassMethod:
                $this->context[AttributeParser::CTX_FUNCTION] = $this->name($node->name);
                break;
        }
    }

    /**
     * @param Node\Name|Node\Identifier|null $name
     * @return string
     */
    private function name($name): string
    {
        if ($name === null) {
            return '';
        }

        return $name->toString();
    }

    /**
     * @param Node\AttributeGroup[] $groups
     * @return \Traversable<AttributePrototype>
     * @throws \Throwable
     */
    private function parse(array $groups): \Traversable
    {
        return $this->parser->parseAttributes($this->file, $groups, $this->context);
    }

    /**
     * @return string
     */
    private function fqn(): string
    {
        $namespace = $this->context[AttributeParser::CTX_NAMESPACE] ?? '';
        $class = $this->context[AttributeParser::CTX_CLASS] ?? '';

        return \trim($namespace . '\\' . $class, '\\');
    }
}
