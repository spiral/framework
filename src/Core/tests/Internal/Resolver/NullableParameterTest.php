<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Internal\Resolver;

use Spiral\Core\BinderInterface;
use Spiral\Tests\Core\Stub\EngineInterface;
use Spiral\Tests\Core\Stub\EngineMarkTwo;

/**
 * Others nullable tests:
 * @see VariadicParameterTest::testNullableVariadicArgument()
 */
class NullableParameterTest extends BaseTest
{
    public function testEmptySignature(): void
    {
        $result = $this->resolveClosure(static fn() => null);

        $this->assertSame([], $result);
    }

    public function testResolveFromContainer(): void
    {
        $binder = $this->constructor->get('binder', BinderInterface::class);
        $binder->bindSingleton(EngineInterface::class, new EngineMarkTwo());

        var_dump($this->createResolver()->container->state->bindings);

        $result = $this->resolveClosure(static fn(EngineInterface $engine) => $engine);

        $this->assertCount(1, $result);
        $this->assertInstanceOf(EngineMarkTwo::class, $result[0]);
    }

    public function testNullableDefaultNull(): void
    {
        $result = $this->resolveClosure(static fn(?string $param = null) => $param);

        $this->assertSame([null], $result);
    }

    public function testNullableWithoutDefaultValue(): void
    {
        $result = $this->resolveClosure(static fn(?object $param) => $param);

        $this->assertSame([null], $result);
    }

    public function testNullableDefaultScalar(): void
    {
        $result = $this->resolveClosure(static fn(?string $param = 'scalar') => $param);

        $this->assertSame(['scalar'], $result);
    }

    public function testNullableAndValueInContainer(): void
    {
        $binder = $this->constructor->get('binder', BinderInterface::class);
        $binder->bindSingleton(EngineInterface::class, new EngineMarkTwo());

        $result = $this->resolveClosure(static fn(?EngineInterface $engine) => $engine);

        $this->assertCount(1, $result);
        $this->assertInstanceOf(EngineMarkTwo::class, $result[0]);
    }

    public function testNullableDefaultScalarAndNamedArgumentNull(): void
    {
        $result = $this->resolveClosure(
            static fn(?string $param = 'scalar') => $param,
            ['param' => null]
        );

        $this->assertSame([null], $result);
    }

    public function testNullableUnionDefaultScalar(): void
    {
        $result = $this->resolveClosure(
            static fn(null|int|string $param = 42) => $param
        );

        $this->assertSame([42], $result);
    }
}
