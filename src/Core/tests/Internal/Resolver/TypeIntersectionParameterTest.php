<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Internal\Resolver;

use Spiral\Tests\Core\Stub\EngineInterface;
use Spiral\Tests\Core\Stub\EngineZIL130;
use Spiral\Tests\Core\Stub\MadeInUssrInterface;

/**
 * Others type intersection tests:
 *
 * @see ExceptionsTest::testVariadicTypeIntersectionParameterAndUnnamedArguments()
 */
final class TypeIntersectionParameterTest extends BaseTest
{
    public function testVariadicTypeIntersectionParameterAndUnnamedArguments(): void
    {
        $result = $this->resolveClosure(
            fn(EngineInterface&MadeInUssrInterface $engines) => $engines,
            $args = [new EngineZIL130()]
        );

        $this->assertSame($args, $result);
    }
}
