<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Internal\Resolver;

/**
 * Others union type tests:
 *
 * @see NullableParameterTest::testNullableUnionDefaultScalar()
 * @see VariadicParameterTest::testVariadicUnionParameterAndUnnamedArguments()
 */
final class UnionTypeParameterTest extends BaseTestCase
{
    public function testScalarOrClassFromContainer(): void
    {
        $this->bindSingleton(\DateTimeInterface::class, $time = new \DateTimeImmutable());

        $result = $this->resolveClosure(
            static fn(string|\DateTimeInterface $time) => null,
            [],
        );

        $this->assertSame([$time], $result);
    }
}
