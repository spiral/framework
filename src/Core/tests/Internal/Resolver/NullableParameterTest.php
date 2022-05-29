<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Internal\Resolver;

use DateTimeInterface;
use RuntimeException;
use Spiral\Tests\Core\Stub\EngineInterface;
use Spiral\Tests\Core\Stub\EngineMarkTwo;

/**
 * Others nullable tests:
 *
 * @see VariadicParameterTest::testNullableVariadicArgument()
 * @see ReferenceParameterTest::testInvokeReferencedArguments()
 */
final class NullableParameterTest extends BaseTest
{
    public function testNullableDefaultNull(): void
    {
        $result = $this->resolveClosure(static fn(?string $param = null) => $param);

        $this->assertSame([null], $result);
    }

    /**
     * Nullable arguments should be searched in container.
     */
    public function testNullableWithoutDefaultValueShouldBeSearchedInContainer(): void
    {
        $this->bindSingleton(EngineInterface::class, $engine = new EngineMarkTwo());

        $result = $this->resolveClosure(static fn(?EngineInterface $engine) => null);

        $this->assertSame([$engine], $result);
    }

    /**
     * If argument for a nullable parameter is not found in container then it should be resolved as `null`.
     */
    public function testNullableClassWithoutDefaultValue(): void
    {
        $result = $this->resolveClosure(static fn(?EngineInterface $param) => $param);

        $this->assertSame([null], $result);
    }

    public function testNullableObjectWithoutDefaultValue(): void
    {
        $result = $this->resolveClosure(static fn(?object $param) => $param);

        $this->assertSame([null], $result);
    }

    public function testNullableScalarWithoutDefaultValue(): void
    {
        $result = $this->resolveClosure(static fn(?int $param) => $param);

        $this->assertSame([null], $result);
    }

    /**
     * Nullable scalars should be set with `null` if not specified by name explicitly.
     */
    public function testNullableDefaultScalar(): void
    {
        $result = $this->resolveClosure(static fn(?string $param = 'scalar') => $param);

        $this->assertSame(['scalar'], $result);
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

    public function testNullableClassThatCreatedWithFail(): void
    {
        $this->bind(DateTimeInterface::class, fn () => throw new RuntimeException('fail!'));

        $result = $this->resolveClosure(
            static fn(?DateTimeInterface $param) => $param
        );

        $this->assertSame([null], $result);
    }

    public function testNotNullableClassThatCreatedWithFail(): void
    {
        $this->bind(DateTimeInterface::class, fn () => throw new RuntimeException('fail!'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('fail!');

        $this->resolveClosure(
            static fn(DateTimeInterface $param) => $param
        );
    }
}
