<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Internal\Resolver;

use DateTimeImmutable;
use Spiral\Core\Exception\Container\ArgumentException;
use Spiral\Core\Exception\Container\NotFoundException;
use Spiral\Tests\Core\Stub\ColorInterface;
use Spiral\Tests\Core\Stub\EngineInterface;
use Spiral\Tests\Core\Stub\EngineMarkTwo;

final class ExceptionsTest extends BaseTest
{
    public function testMissingRequiredTypedParameter(): void
    {
        $this->bindSingleton(EngineInterface::class, new EngineMarkTwo());

        $this->expectException(ArgumentException::class);

        $this->resolveClosure(
            static fn (EngineInterface $engine, string $two) => null
        );
    }

    public function testMissingRequiredNotTypedParameter(): void
    {
        $this->bindSingleton(EngineInterface::class, new EngineMarkTwo());

        $this->expectException(ArgumentException::class);

        $this->resolveClosure(
            static fn (EngineInterface $engine, $two) => null
        );
    }

    public function testNotFoundException(): void
    {
        $this->bindSingleton(EngineInterface::class, new EngineMarkTwo());

        $this->expectException(NotFoundException::class);

        $this->resolveClosure(
            static fn (EngineInterface $engine, ColorInterface $color) => null
        );
    }

    public function testWrongNamedParam(): void
    {
        $this->expectException(\Throwable::class);

        $this->resolveClosure(fn (EngineInterface $engine) => null, ['engine' => new DateTimeImmutable()]);
    }

    /**
     * Required `object` type should not be requested from the container
     */
    public function testRequiredObjectTypeWithoutInstance(): void
    {
        $this->expectException(ArgumentException::class);

        $this->resolveClosure(
            static fn (object $engine) => null
        );
    }

    // TODO

    public function testWrongNamedParamWithValueInContainer(): void
    {
        $this->bindSingleton(EngineInterface::class, $engine = new EngineMarkTwo());

        $result = $this->resolveClosure(
            static fn (EngineInterface $engine) => null,
            ['engine' => new DateTimeImmutable()]
        );

        $this->expectException(\Throwable::class);
        // todo OR
        $this->assertSame([$engine], $result);
    }

    public function testArrayArgumentWithUnnamedType(): void
    {
        $this->bindSingleton(EngineInterface::class, $engine = new EngineMarkTwo());

        // todo OR
        $this->expectException(ArgumentException::class);

        $this->resolveClosure(
            static fn (array $arg) => null,
            [['test']]
        );
    }

    public function testCallableParameterWithClosureArg(): void
    {
        $result = $this->resolveClosure(
            static fn (callable $arg) => null,
            [$callable = fn () => true]
        );

        // $this->expectException(MissingRequiredArgumentException::class);
        // TODO
        $this->assertSame([$callable], $result);
    }

    public function testIterableArgumentWithUnnamedType(): void
    {
        $result = $this->resolveClosure(
            static fn (iterable $arg) => null,
            [$iterable = new \SplStack()]
        );

        // $this->expectException(MissingRequiredArgumentException::class);
        // TODO
        $this->assertSame([$iterable], $result); // todo arg as array
    }

    public function testUnnamedScalarParam(): void
    {
        $result = $this->resolveClosure(
            static fn () => null,
            ['scalar']
        );

        // $this->expectException(InvalidArgumentException::class);
        // TODO?
        $this->assertSame([], $result);
    }
}
