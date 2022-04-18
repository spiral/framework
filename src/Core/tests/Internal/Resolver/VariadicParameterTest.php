<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Internal\Resolver;

use Spiral\Tests\Core\Stub\EngineInterface;
use Spiral\Tests\Core\Stub\EngineMarkTwo;
use Spiral\Tests\Core\Stub\EngineVAZ2101;
use Spiral\Tests\Core\Stub\EngineZIL130;
use Spiral\Tests\Core\Stub\MadeInUssrInterface;
use stdClass;

/**
 * Others variadic parameter tests:
 *
 * @see ReferenceParameterTest::testReferencedVariadicParameterAndUnnamedArguments()
 * @see ReferenceParameterTest::testReferencedVariadicParameterWithNamedArgument()
 * @see ReferenceParameterTest::testInvokeReferencedArguments()
 */
final class VariadicParameterTest extends BaseTest
{
    /**
     * A values collection for a variadic argument can be passed as an array in a named parameter.
     */
    public function testAloneScalarVariadicParameterAndNamedArrayArgument(): void
    {
        $result = $this->resolveClosure(
            fn (int ...$var) => $var,
            ['var' => [1, 2, 3], new stdClass()]
        );

        $this->assertSame([1, 2, 3], $result);
    }

    public function testAloneScalarVariadicParameterAndNamedAssocArrayArgument(): void
    {
        $result = $this->resolveClosure(
            fn (string $foo, string ...$bar) => null,
            ['foo' => 'foo', 'bar' => ['foo' => 'baz', '0' => 'fiz']]
        );

        $this->assertSame(['foo', 'baz', 'fiz'], $result);
    }

    public function testAloneScalarVariadicParameterAndNamedScalarArgument(): void
    {
        $result = $this->resolveClosure(
            fn (int ...$var) => null,
            ['var' => 42, new stdClass()]
        );

        $this->assertSame([42], $result);
    }

    /**
     * If type of a variadic argument is a class and named argument with values collection is not set then Resolver
     * will search for objects by class name among all unnamed parameters.
     */
    public function testVariadicParameterAndUnnamedArguments(): void
    {
        $result = $this->resolveClosure(
            fn (EngineInterface ...$engines) => $engines,
            [new EngineZIL130(), new EngineVAZ2101(), new stdClass(), new EngineMarkTwo(), new stdClass()]
        );

        $this->assertCount(3, $result);
        \array_walk($result, function (mixed $value): void {
            $this->assertInstanceOf(EngineInterface::class, $value);
        });
    }

    public function testVariadicObjectParameterAndUnnamedArguments(): void
    {
        $result = $this->resolveClosure(
            fn (object ...$engines) => $engines,
            $data = [new EngineZIL130(), new EngineVAZ2101(), new stdClass(), new EngineMarkTwo(), new stdClass()]
        );

        $this->assertCount(5, $result);
        $this->assertSame($data, $result);
    }

    public function testVariadicTypeIntersectionParameterAndUnnamedArguments(): void
    {
        $result = $this->resolveClosure(
            fn (EngineInterface&MadeInUssrInterface ...$engines) => $engines,
            [new EngineZIL130(), new EngineVAZ2101(), new stdClass(), new EngineMarkTwo(), new stdClass()]
        );

        $this->assertCount(2, $result);
        $this->assertInstanceOf(EngineZIL130::class, $result[0]);
        $this->assertInstanceOf(EngineVAZ2101::class, $result[1]);
    }

    /**
     * If a calling method have an untyped variadic argument then all remaining unnamed parameters will be passed.
     */
    public function testVariadicMixedArgumentWithMixedParams(): void
    {
        $result = $this->resolveClosure(
            static fn (...$engines) => $engines,
            [new EngineZIL130(), new EngineVAZ2101(), new EngineMarkTwo(), new stdClass()]
        );

        $this->assertCount(4, $result);
        \array_walk($result, function (mixed $value): void {
            $this->assertInstanceOf(EngineInterface::class, $value);
        });
    }

    // todo
    /**
     * Any unnamed parameter can only be an object. Scalar, array, null and other values can only be named parameters.
     */
    public function testVariadicStringArgumentWithUnnamedStringsParams(): void
    {
        $result = $this->resolveClosure(
            fn (string ...$engines) => $engines,
            ['str 1', 'str 2', 'str 3']
        );

        // TODO
        $this->expectException(\Exception::class);
        // OR
        $this->assertCount(3, $result);
        \array_walk($result, $this->assertIsString(...));
    }

    /**
     * In the absence of other values to a nullable variadic argument `null` is not passed by default.
     */
    public function testNullableVariadicArgument(): void
    {
        $result = $this->resolveClosure(
            fn (?EngineInterface ...$engines) => $engines,
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
            static fn (EngineInterface ...$engines) => $engines,
            []
        );

        $this->assertCount(0, $result);
    }
}
