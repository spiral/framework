<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Internal\Resolver;

use Spiral\Tests\Core\Stub\EngineInterface;
use Spiral\Tests\Core\Stub\EngineMarkTwo;
use Spiral\Tests\Core\Stub\EngineZIL130;

/**
 * @see NullableParameterTest::testNullableDefaultScalarAndNamedArgumentNull()
 * @see ReferenceParameterTest::testInvokeReferencedArguments
 */
final class NamedArgumentsTest extends BaseTestCase
{
    /**
     * In this case, second argument will be set from parameters by name, and first argument from container.
     */
    public function testNamedArgumentWithValueFromContainer(): void
    {
        $this->bindSingleton(EngineInterface::class, $engineA = new EngineMarkTwo());

        $result = $this->resolveClosure(
            static fn(EngineInterface $engine1, EngineInterface $engine2) => null,
            ['engine2' => ($engineB = new EngineZIL130())],
        );

        $this->assertSame([$engineA, $engineB], $result);
    }

    public function testNamedArgumentsNotSorted(): void
    {
        $this->bindSingleton(EngineInterface::class, $engineA = new EngineMarkTwo());

        $result = $this->resolveClosure(
            static fn(EngineInterface $engine1, EngineInterface $engine2, EngineInterface $engine3) => null,
            [
                'engine2' => ($engineB = new EngineZIL130()),
                'engine3' => ($engineC = new EngineZIL130()),
                'engine1' => ($engineA = new EngineZIL130()),
            ],
        );

        $this->assertSame([$engineA, $engineB, $engineC], $result);
    }
}
