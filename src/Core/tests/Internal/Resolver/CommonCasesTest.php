<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Internal\Resolver;

use Spiral\Core\Container\Autowire;
use Spiral\Core\Exception\Container\ContainerException;
use Spiral\Tests\Core\Stub\EngineInterface;
use Spiral\Tests\Core\Stub\EngineMarkTwo;
use Spiral\Tests\Core\Stub\EnumObject;
use Spiral\Tests\Core\Stub\NewObjectInParam;
use Spiral\Tests\Core\Stub\TestTrait;

final class CommonCasesTest extends BaseTestCase
{
    public function testEmptySignature(): void
    {
        $result = $this->resolveClosure(static fn() => null);

        $this->assertSame([], $result);
    }

    public function testResolveFromContainer(): void
    {
        $this->bindSingleton(EngineInterface::class, new EngineMarkTwo());

        $result = $this->resolveClosure(static fn(EngineInterface $engine) => null);

        $this->assertCount(1, $result);
        $this->assertInstanceOf(EngineMarkTwo::class, $result[0]);
    }

    public function testAutowireArgumentByPosition(): void
    {
        $result = $this->resolveClosure(
            static fn(string $foo = 'foo', ?EngineInterface $engine = null) => null,
            [1 => new Autowire(EngineMarkTwo::class)],
        );

        $this->assertCount(2, $result);
        $this->assertSame('foo', $result[0]);
        $this->assertInstanceOf(EngineMarkTwo::class, $result[1]);
    }

    public function testResolveClassWithDefaultObjectValue(): void
    {
        $result = $this->resolveClassConstructor(
            NewObjectInParam::class,
        );

        $this->assertCount(1, $result);
        $this->assertInstanceOf(\stdClass::class, $result[0]);
    }

    /**
     * @see \Spiral\Tests\Core\Internal\Factory\CommonCasesTest::testNotInstantiableEnum()
     */
    public function testNotInstantiableEnum(): void
    {
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('Enum `Spiral\Tests\Core\Stub\EnumObject` can not be constructed.');

        $this->resolveClosure(
            static function (EnumObject $enum): void {},
        );
    }

    public function testNotInstantiableTrait(): void
    {
        self::expectException(ContainerException::class);
        self::expectExceptionMessage("Can't autowire `Spiral\Tests\Core\Stub\TestTrait`: class or injector not found.");

        $this->resolveClosure(
            static function (TestTrait $enum): void {},
        );
    }
}
