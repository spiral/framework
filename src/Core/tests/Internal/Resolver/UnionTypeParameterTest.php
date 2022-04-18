<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Internal\Resolver;

use DateTimeImmutable;
use DateTimeInterface;

/**
 * Others union type tests:
 *
 * @see NullableParameterTest::testNullableUnionDefaultScalar()
 * @see VariadicParameterTest::testVariadicUnionParameterAndUnnamedArguments()
 */
final class UnionTypeParameterTest extends BaseTest
{
    public function testScalarOrClassFromContainer(): void
    {
        $this->bindSingleton(DateTimeInterface::class, $time = new DateTimeImmutable());

        $result = $this->resolveClosure(
            static fn(string|DateTimeInterface $time) => null,
            []
        );

        $this->assertSame([$time], $result);
    }
}
