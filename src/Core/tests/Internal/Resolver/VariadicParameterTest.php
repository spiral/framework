<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Internal\Resolver;

use Spiral\Core\Exception\Resolver\ResolvingException;
use Spiral\Tests\Core\Stub\EngineInterface;
use Spiral\Tests\Core\Stub\EngineMarkTwo;
use Spiral\Tests\Core\Stub\EngineVAZ2101;
use Spiral\Tests\Core\Stub\EngineZIL130;
use stdClass;

/**
 * Others variadic parameter tests:
 *
 * @see ReferenceParameterTest::testReferencedVariadicParameterAndUnnamedArguments()
 * @see ReferenceParameterTest::testReferencedVariadicParameterWithNamedArgument()
 * @see ReferenceParameterTest::testInvokeReferencedArguments()
 * @see TypeIntersectionParameterTest::testVariadicTypeIntersectionParameterAndUnnamedArguments()
 */
final class VariadicParameterTest extends BaseTestCase
{
    /**
     * A values collection for a variadic parameter can be passed as an array in a named argument.
     */
    public function testAloneScalarVariadicParameterAndNamedArrayArgument(): void
    {
        $result = $this->resolveClosure(
            fn(int ...$var) => $var,
            ['var' => [1, 2, 3]],
        );

        $this->assertSame([1, 2, 3], $result);
    }

    public function testScalarVariadicParameterAndNamedAssocArrayArgument(): void
    {
        $result = $this->resolveClosure(
            fn(string $foo, string ...$bar) => null,
            ['foo' => 'foo', 'bar' => ['foo' => 'baz', 'bar' => 'fiz']],
        );

        $this->assertSame(['foo', 'foo' => 'baz', 'bar' => 'fiz'], $result);
    }

    public function testScalarVariadicParameterAndMixedArgumentsArray(): void
    {
        $result = $this->resolveClosure(
            $fn = fn(string ...$bar) => $bar,
            ['bar' => ($args = ['foo1', 'foo' => 'baz', 'bar' => 'fiz'])],
        );

        $this->assertSame(['foo1', 'foo' => 'baz', 'bar' => 'fiz'], $result);
        $this->assertSame(['foo1', 'foo' => 'baz', 'bar' => 'fiz'], $fn(...$args));
    }

    public function testScalarVariadicParameterAndWrongMixedArgumentsArray(): void
    {
        $this->expectException(ResolvingException::class);
        $this->expectExceptionMessage(
            'Cannot use positional argument after named argument during unpacking named variadic argument'
        );

        $this->resolveClosure(
            fn(string ...$bar) => $bar,
            ['bar' => ['foo1', 'foo' => 'baz', 'bar' => 'fiz', 'baz2']],
        );
    }

    public function testScalarVariadicParameterAndNamedScalarArgumentNotInArray(): void
    {
        $result = $this->resolveClosure(
            fn(int ...$var) => null,
            ['var' => 42]
        );

        $this->assertSame([42], $result);
    }

    public function testVariadicObjectParameterAndPositionArguments(): void
    {
        $result = $this->resolveClosure(
            fn(object ...$engines) => $engines,
            [$data = [new EngineZIL130(), new EngineVAZ2101(), new stdClass(), new EngineMarkTwo(), new stdClass()]]
        );

        $this->assertCount(5, $result);
        $this->assertSame($data, $result);
    }

    /**
     * If a calling method have an untyped variadic argument then all remaining unnamed parameters will be passed.
     */
    public function testVariadicMixedParameterWithMixedPositionArguments(): void
    {
        $result = $this->resolveClosure(
            static fn(mixed ...$engines) => $engines,
            [[new EngineZIL130(), new EngineVAZ2101(), new EngineMarkTwo(), new stdClass()]]
        );

        $this->assertCount(4, $result);
        $checked = true;
        \array_walk($result, function (mixed $value) use (&$checked): void {
            $checked = $checked && ($value instanceof EngineInterface);
        });
        // The Resolver doesn't check arguments type
        $this->assertFalse($checked);
    }

    /**
     * In the absence of other values to a nullable variadic argument `null` is not passed by default.
     */
    public function testNullableVariadicArgument(): void
    {
        $result = $this->resolveClosure(
            fn(?EngineInterface ...$engines) => $engines,
            []
        );

        $this->assertSame([], $result);
    }

    /**
     * If the variadic parameter type is a class and its value is not passed as argument, then no arguments will be
     * passed as result, despite the fact that the container has a corresponding value.
     */
    public function testVariadicParamWithoutArgumentsButWithContainer(): void
    {
        $this->bindSingleton(EngineInterface::class, new EngineMarkTwo());

        $result = $this->resolveClosure(
            static fn(EngineInterface ...$engines) => $engines,
            []
        );

        $this->assertCount(0, $result);
    }

    /**
     * Variadic arguments should be passed in a one array
     */
    public function testVariadicParameterAndUnpackedArguments(): void
    {
        $result = $this->resolveClosure(
            fn(EngineInterface ...$engines) => $engines,
            [new EngineZIL130(), new EngineVAZ2101(), new EngineMarkTwo()]
        );

        $this->assertCount(1, $result);
    }
}
