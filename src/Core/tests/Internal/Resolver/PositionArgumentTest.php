<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Internal\Resolver;

use DateTimeImmutable;
use Spiral\Tests\Core\Stub\EngineInterface;
use Spiral\Tests\Core\Stub\EngineMarkTwo;
use Spiral\Tests\Core\Stub\EngineVAZ2101;
use Spiral\Tests\Core\Stub\EngineZIL130;

/**
 * @see VariadicParameterTest::testVariadicObjectParameterAndPositionArguments()
 */
final class PositionArgumentTest extends BaseTest
{
    /**
     * In this case, first argument will be pulled from container, second - from args list.
     */
    public function testSecondPositionArgument(): void
    {
        $this->bindSingleton(EngineInterface::class, $engineA = new EngineMarkTwo());

        $result = $this->resolveClosure(
            static fn(EngineInterface $engine1, EngineInterface $engine2) => null,
            [1 => ($engineB = new EngineZIL130())]
        );

        $this->assertSame([$engineA, $engineB], $result);
    }

    public function testArrayParamAndNumericArgument(): void
    {
        $result = $this->resolveClosure(
            static fn(array $arg) => null,
            [['test']]
        );

        $this->assertSame([['test']], $result);
    }

    public function testCallableParameterAndNumericClosureArgument(): void
    {
        $result = $this->resolveClosure(
            static fn(callable $callable) => null,
            [$callable = fn() => true]
        );

        $this->assertSame([$callable], $result);
    }

    public function testNumericIterableArgument(): void
    {
        $result = $this->resolveClosure(
            static fn(iterable $arg) => null,
            [$iterable = new \SplStack()]
        );

        $this->assertSame([$iterable], $result); // todo arg as array
    }

    public function testUnnamedScalarParam(): void
    {
        $result = $this->resolveClosure(
            static fn() => null,
            ['scalar']
        );

        // TODO or empty?
        $this->assertSame(['scalar'], $result);
    }

    /**
     * Object type may be passed as unnamed parameter
     */
    public function testInvokeWithObjectType(): void
    {
        $result = $this->resolveClosure(
            static fn(object $object) => null,
            [$object = new DateTimeImmutable()]
        );

        $this->assertSame([$object], $result);
    }

    /**
     * All position arguments will be passed as is (with trailed arguments)
     */
    public function testTrailedArguments(): void
    {
        $result = $this->resolveClosure(
            static fn(?EngineInterface $engine, $id = 'test') => null,
            [
                new EngineMarkTwo(),
                new DateTimeImmutable(),
                new DateTimeImmutable(),
                new EngineVAZ2101(),
            ]
        );

        $this->assertCount(4, $result);
    }

    public function testTrailedArgumentsOnEmptySignature(): void
    {
        $result = $this->resolveClosure(
            static fn() => null,
            $args = [
                new EngineMarkTwo(),
                new DateTimeImmutable(),
                new DateTimeImmutable(),
                new EngineVAZ2101(),
            ]
        );

        $this->assertCount(4, $result);
        $this->assertSame($args, $result);
    }
}
