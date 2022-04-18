<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Internal\Resolver;

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
 * @see ExceptionsTest::testVariadicParameterAndUnpackedArguments()
 */
final class VariadicParameterTest extends BaseTest
{
    /**
     * A values collection for a variadic argument can be passed as an array in a named parameter.
     */
    public function testAloneScalarVariadicParameterAndNamedArrayArgument(): void
    {
        $result = $this->resolveClosure(
            fn(int ...$var) => $var,
            ['var' => [1, 2, 3], new stdClass()]
        );

        $this->assertSame([1, 2, 3], $result);
    }

    public function testAloneScalarVariadicParameterAndNamedAssocArrayArgument(): void
    {
        $result = $this->resolveClosure(
            fn(string $foo, string ...$bar) => null,
            ['foo' => 'foo', 'bar' => ['foo' => 'baz', '0' => 'fiz']]
        );

        $this->assertSame(['foo', 'baz', 'fiz'], $result);
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
            $data = [[new EngineZIL130(), new EngineVAZ2101(), new stdClass(), new EngineMarkTwo(), new stdClass()]]
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
        \array_walk($result, function (mixed $value): void {
            $this->assertInstanceOf(EngineInterface::class, $value);
        });
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
}
