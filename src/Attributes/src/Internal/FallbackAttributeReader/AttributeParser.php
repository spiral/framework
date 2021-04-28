<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Attributes\Internal\FallbackAttributeReader;

use PhpParser\ConstExprEvaluationException;
use PhpParser\ConstExprEvaluator;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Expr;
use PhpParser\Node\Scalar;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use Spiral\Attributes\Internal\Exception;

final class AttributeParser
{
    /**
     * @var string
     */
    public const CTX_FUNCTION = '__FUNCTION__';

    /**
     * @var string
     */
    public const CTX_NAMESPACE = '__NAMESPACE__';

    /**
     * @var string
     */
    public const CTX_CLASS = '__CLASS__';

    /**
     * @var string
     */
    public const CTX_TRAIT = '__TRAIT__';
    /**
     * @var string
     */
    private const ERROR_NAMED_ARGUMENTS_ORDER = 'Cannot use positional argument after named argument';

    /**
     * @var string
     */
    private const ERROR_BAD_CONSTANT_EXPRESSION = 'Constant expression contains invalid operations';

    /**
     * @var string
     */
    private const ERROR_BAD_CONSTANT = 'Undefined constant %s';

    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var NodeTraverser
     */
    private $resolver;

    /**
     * @param Parser|null $parser
     */
    public function __construct(Parser $parser = null)
    {
        $this->parser = $parser ?? $this->createParser();

        $this->resolver = new NodeTraverser();
        $this->resolver->addVisitor(new NameResolver());
    }

    /**
     * @param string $file
     * @return AttributeFinderVisitor
     */
    public function parse(string $file): AttributeFinderVisitor
    {
        $ast = $this->parser->parse($this->read($file));

        $finder = new AttributeFinderVisitor($file, $this);

        $traverser = new NodeTraverser();
        $traverser->addVisitor($finder);

        $traverser->traverse(
            $this->resolver->traverse($ast)
        );

        return $finder;
    }

    /**
     * @param string $file
     * @param AttributeGroup[] $groups
     * @param array $context
     * @return \Traversable<AttributePrototype>
     * @throws \Throwable
     */
    public function parseAttributes(string $file, array $groups, array $context): \Traversable
    {
        $eval = new ConstExprEvaluator($this->evaluator($file, $context));

        foreach ($groups as $group) {
            foreach ($group->attrs as $attr) {
                $arguments = $this->parseAttributeArguments($attr, $file, $eval);

                yield new AttributePrototype($attr->name->toString(), $arguments);
            }
        }
    }

    /**
     * @return Parser
     */
    private function createParser(): Parser
    {
        $factory = new ParserFactory();

        return $factory->create(ParserFactory::ONLY_PHP7);
    }

    /**
     * @param string $file
     * @return string
     */
    private function read(string $file): string
    {
        if (!\is_readable($file)) {
            throw new \InvalidArgumentException('Unable to read file "' . $file . '"');
        }

        return \file_get_contents($file);
    }

    /**
     * @param string $file
     * @param array $context
     * @return \Closure
     */
    private function evaluator(string $file, array $context): \Closure
    {
        return static function (Expr $expr) use ($file, $context) {
            switch (\get_class($expr)) {
                case Scalar\MagicConst\File::class:
                    return $file;

                case Scalar\MagicConst\Dir::class:
                    return \dirname($file);

                case Scalar\MagicConst\Line::class:
                    return $expr->getStartLine();

                case Scalar\MagicConst\Method::class:
                    $namespace = $context[self::CTX_NAMESPACE] ?? '';
                    $function = $context[self::CTX_FUNCTION] ?? '';

                    return \ltrim($namespace . '\\' . $function, '\\');

                case Expr\ClassConstFetch::class:
                    $constant = $expr->name->toString();
                    $class = $expr->class->toString();

                    if (\strtolower($constant) === 'class') {
                        return $class;
                    }

                    $definition = $class . '::' . $constant;

                    if (!\defined($definition)) {
                        $exception = new \ParseError(\sprintf(self::ERROR_BAD_CONSTANT, $definition));
                        throw Exception::withLocation($exception, $file, $expr->getStartLine());
                    }

                    return \constant($definition);
            }

            if ($expr instanceof Scalar\MagicConst) {
                return $context[$expr->getName()] ?? '';
            }

            $exception = new \ParseError(self::ERROR_BAD_CONSTANT_EXPRESSION);
            throw Exception::withLocation($exception, $file, $expr->getStartLine());
        };
    }

    /**
     * @param Attribute $attr
     * @param string $file
     * @param ConstExprEvaluator $eval
     * @return array
     * @throws ConstExprEvaluationException
     * @throws \Throwable
     */
    private function parseAttributeArguments(Attribute $attr, string $file, ConstExprEvaluator $eval): array
    {
        $hasNamedArguments = false;
        $arguments = [];

        foreach ($attr->args as $argument) {
            $value = $eval->evaluateDirectly($argument->value);

            if ($argument->name === null) {
                $arguments[] = $value;

                if ($hasNamedArguments) {
                    $exception = new \ParseError(self::ERROR_NAMED_ARGUMENTS_ORDER);
                    throw Exception::withLocation($exception, $file, $argument->getStartLine());
                }

                continue;
            }

            $hasNamedArguments = true;
            $arguments[$argument->name->toString()] = $value;
        }

        return $arguments;
    }
}
