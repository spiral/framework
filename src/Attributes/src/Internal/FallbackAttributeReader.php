<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Attributes\Internal;

use PhpParser\Parser;
use Spiral\Attributes\Internal\FallbackAttributeReader\AttributeParser;
use Spiral\Attributes\Internal\FallbackAttributeReader\AttributePrototype;
use Spiral\Attributes\Internal\Instantiator\InstantiatorInterface;

/**
 * @internal FallbackAttributeReader is an internal library class, please do not use it in your code.
 * @psalm-internal Spiral\Attributes
 */
final class FallbackAttributeReader extends AttributeReader
{
    /**
     * @var int
     */
    private const KEY_CLASSES = 0x00;

    /**
     * @var int
     */
    private const KEY_CONSTANTS = 0x01;

    /**
     * @var int
     */
    private const KEY_PROPERTIES = 0x02;

    /**
     * @var int
     */
    private const KEY_FUNCTIONS = 0x03;

    /**
     * @var int
     */
    private const KEY_PARAMETERS = 0x04;

    /**
     * @var AttributeParser
     */
    private $parser;

    /**
     * @psalm-type ClassName       = string
     * @psalm-type ConstantName    = string
     * @psalm-type PropertyName    = string
     * @psalm-type ParameterName   = string
     *
     * @psalm-type FunctionEndLine = positive-int
     *
     * @psalm-type AttributesList  = array<AttributePrototype>
     *
     * @var non-empty-array {
     *  0: array<ClassName, AttributesList>,
     *  1: array<ClassName, array<ConstantName, AttributesList>>,
     *  2: array<ClassName, array<PropertyName, AttributesList>>,
     *  3: array<FunctionEndLine, AttributesList>,
     *  4: array<FunctionEndLine, array<ParameterName, AttributesList>>
     * }
     */
    private $attributes = [
        self::KEY_CLASSES    => [],
        self::KEY_CONSTANTS  => [],
        self::KEY_PROPERTIES => [],
        self::KEY_FUNCTIONS  => [],
        self::KEY_PARAMETERS => [],
    ];

    /**
     * @param InstantiatorInterface|null $instantiator
     * @param Parser|null $parser
     */
    public function __construct(InstantiatorInterface $instantiator = null, Parser $parser = null)
    {
        $this->parser = new AttributeParser($parser);

        parent::__construct($instantiator);
    }

    /**
     * {@inheritDoc}
     */
    protected function getClassAttributes(\ReflectionClass $class, ?string $name): iterable
    {
        // 1) Can not parse internal classes
        // 2) Anonymous classes don't support attributes (PHP semantic)
        if ($class->isInternal() || $class->isAnonymous()) {
            return [];
        }

        $attributes = $this->parseAttributes($class->getFileName(), self::KEY_CLASSES);

        return $this->format($attributes[$class->getName()] ?? [], $name, $class);
    }

    /**
     * {@inheritDoc}
     */
    protected function getFunctionAttributes(\ReflectionFunctionAbstract $function, ?string $name): iterable
    {
        // Can not parse internal functions
        if ($function->isInternal()) {
            return [];
        }

        $attributes = $this->parseAttributes($function->getFileName(), self::KEY_FUNCTIONS);
        $attributes = $this->extractFunctionAttributes($attributes, $function);

        return $this->format($attributes, $name, $function);
    }

    /**
     * {@inheritDoc}
     */
    protected function getPropertyAttributes(\ReflectionProperty $property, ?string $name): iterable
    {
        $class = $property->getDeclaringClass();

        // Can not parse property of internal class
        if ($class->isInternal()) {
            return [];
        }

        $attributes = $this->parseAttributes($class->getFileName(), self::KEY_PROPERTIES);

        return $this->format($attributes[$class->getName()][$property->getName()] ?? [], $name, $property);
    }

    /**
     * {@inheritDoc}
     */
    protected function getConstantAttributes(\ReflectionClassConstant $const, ?string $name): iterable
    {
        $class = $const->getDeclaringClass();

        // Can not parse internal classes
        if ($class->isInternal()) {
            return [];
        }

        $attributes = $this->parseAttributes($class->getFileName(), self::KEY_CONSTANTS);

        return $this->format($attributes[$class->getName()][$const->getName()] ?? [], $name, $const);
    }

    /**
     * {@inheritDoc}
     */
    protected function getParameterAttributes(\ReflectionParameter $param, ?string $name): iterable
    {
        $function = $param->getDeclaringFunction();

        // Can not parse parameter of internal function
        if ($function->isInternal()) {
            return [];
        }

        $attributes = $this->parseAttributes($function->getFileName(), self::KEY_PARAMETERS);
        $attributes = $this->extractFunctionAttributes($attributes, $function);

        return $this->format($attributes[$param->getName()] ?? [], $name, $param);
    }

    /**
     * @psalm-type Context = FallbackAttributeReader::KEY_*
     *
     * @param string $file
     * @param Context $context
     * @return array
     */
    private function parseAttributes(string $file, int $context): array
    {
        if (!isset($this->attributes[$file])) {
            $found = $this->parser->parse($file);

            $this->attributes[$file] = [
                self::KEY_CLASSES    => $found->getClasses(),
                self::KEY_FUNCTIONS  => $found->getFunctions(),
                self::KEY_CONSTANTS  => $found->getConstants(),
                self::KEY_PROPERTIES => $found->getProperties(),
                self::KEY_PARAMETERS => $found->getParameters(),
            ];
        }

        return $this->attributes[$file][$context];
    }

    /**
     * @param AttributePrototype[] $attributes
     * @param class-string|null $name
     * @return iterable<\ReflectionClass, array>
     */
    private function format(iterable $attributes, ?string $name, \Reflector $context): iterable
    {
        foreach ($attributes as $prototype) {
            if ($prototype->name !== $name && $name !== null && !\is_subclass_of($prototype->name, $name)) {
                continue;
            }

            $this->assertClassExists($prototype->name, $context);

            yield new \ReflectionClass($prototype->name) => $prototype->params;
        }
    }

    /**
     * @param array $attributes
     * @param \ReflectionFunctionAbstract $function
     * @return array
     */
    private function extractFunctionAttributes(array $attributes, \ReflectionFunctionAbstract $function): array
    {
        /**
         * We cannot use the function start line because it is different for
         * the PHP and nikic/php-parser AST.
         *
         * For example:
         * <code>
         *  1. | #[ExampleAttribute]
         *  2. | #[ExampleAttribute]
         *  3. | function example() { ... }
         * </code>
         *
         * In this case, the PHP {@see \ReflectionFunction} will return:
         * <code>
         *  $reflection->getStartLine(); // 3 (real start of function)
         * </code>
         *
         * However, nikic/php-parser returns:
         * <code>
         *  $ast->getStartLine(); // 1 (the line starts from the first attribute)
         * </code>
         */
        $line = $function->getEndLine();

        if ($result = $attributes[$line] ?? null) {
            return $result;
        }

        /**
         * Workaround for those cases when the ";" is on a new line
         * (nikic/php-parser bug related to terminal line).
         *
         * For example:
         * <code>
         *  1. | $function = #[ExampleAttribute]
         *  2. |     fn() => 42
         *  3. | ;
         * </code>
         *
         * In this case, the PHP {@see \ReflectionFunction} will return:
         * <code>
         *  $reflection->getEndLine(); // 3 (real end of function)
         * </code>
         *
         * However, nikic/php-parser returns:
         * <code>
         *  $ast->getEndLine(); // 2 (last significant character of a function)
         * </code>
         */
        if ($function->isClosure()) {
            while ($line-- > $function->getStartLine()) {
                if ($result = $attributes[$line] ?? null) {
                    return $result;
                }
            }
        }

        return [];
    }
}
