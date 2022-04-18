<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Internal\Resolver;

use DateTimeImmutable;
use Spiral\Tests\Core\Stub\EngineInterface;
use Spiral\Tests\Core\Stub\EngineMarkTwo;
use Spiral\Tests\Core\Stub\EngineVAZ2101;
use Spiral\Tests\Core\Stub\EngineZIL130;
use Spiral\Tests\Core\Stub\LightEngine;
use stdClass;

final class RegularCasesTest extends BaseTest
{
    /**
     * An object for a typed parameter can be specified in arguments without named key and without following the order.
     */
    public function testCustomDependency(): void
    {
        $this->bindSingleton(EngineInterface::class, $engineA = new EngineMarkTwo());

        $result = $this->resolveClosure(
            fn (EngineInterface $engine) => $engine,
            [new stdClass(), $engineB = new EngineZIL130(), new DateTimeImmutable()]
        );

        $this->assertSame([$engineB], $result);
    }

    /**
     * In this case, first argument will be set from parameters, and second argument from container.
     */
    public function testTwoEqualCustomArgumentsWithOneCustom(): void
    {
        $this->bindSingleton(EngineInterface::class, $engineA = new EngineMarkTwo());

        $result = $this->resolveClosure(
            static fn (EngineInterface $engine1, EngineInterface $engine2) => null,
            [$engineB = new EngineZIL130()]
        );

        $this->assertSame([$engineB, $engineA], $result);
    }

    /**
     * In this case, second argument will be set from parameters by name, and first argument from container.
     */
    public function testTwoEqualCustomArgumentsWithOneCustomNamedParameter1(): void
    {
        $this->bindSingleton(EngineInterface::class, $engineA = new EngineMarkTwo());

        $result = $this->resolveClosure(
            static fn (EngineInterface $engine1, EngineInterface $engine2) => null,
            ['engine2' => ($engineB = new EngineZIL130())]
        );

        $this->assertSame([$engineA, $engineB], $result);
    }

    public function testTwoEqualCustomArgumentsWithOneCustomNamedParameter2(): void
    {
        $this->bindSingleton(EngineInterface::class, $engineA = new EngineMarkTwo());

        $result = $this->resolveClosure(
            static fn (EngineInterface $engine1, EngineInterface $engine2) => null,
            ['engine1' => ($engineB = new EngineZIL130())]
        );

        $this->assertSame([$engineB, $engineA], $result);
    }

    /**
     * Values for arguments are not matched by the greater similarity of parameter types and arguments, but simply pass
     * in order as is.
     */
    public function testExtendedArgumentsWithOneCustomNamedParameter(): void
    {
        $this->bindSingleton(LightEngine::class, $engineA = new EngineVAZ2101());

        $result = $this->resolveClosure(
            static fn (EngineInterface $engine1, LightEngine $engine2) => null,
            [
                $engineB = new EngineMarkTwo(), // LightEngine, EngineInterface
                $engineC = new EngineZIL130(), // EngineInterface
            ]
        );

        $this->assertSame([$engineB, $engineA], $result);
    }

    /**
     * Arguments that were passed but were not used on resolving will not be appended to the resolved arguments list.
     */
    public function testAppendingUnusedParams(): void
    {
        $result = $this->resolveClosure(
            static fn (?EngineInterface $engine, $id = 'test') => null,
            [
                new DateTimeImmutable(),
                new DateTimeImmutable(),
                new EngineMarkTwo(),
                'named' => new EngineVAZ2101(),
            ]
        );

        $this->assertCount(2, $result);
    }

    /**
     * Object type may be passed as unnamed parameter
     */
    public function testInvokeWithObjectType(): void
    {
        $result = $this->resolveClosure(
            static fn (object $object) => null,
            [$object = new DateTimeImmutable()]
        );

        $this->assertSame([$object], $result);
    }
}
