<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Internal\Resolver;

use Spiral\Core\Exception\Container\ArgumentException;
use Spiral\Tests\Core\Stub\EngineInterface;
use Spiral\Tests\Core\Stub\EngineMarkTwo;
use Spiral\Tests\Core\Stub\EngineVAZ2101;
use Spiral\Tests\Core\Stub\EngineZIL130;
use Spiral\Tests\Core\Stub\MadeInUssrInterface;
use stdClass;

final class TypeIntersectionParameterTest extends BaseTest
{
    public function testTypeIntersectionParameterAndUnnamedArgument(): void
    {
        $result = $this->resolveClosure(
            fn(EngineInterface&MadeInUssrInterface $engines) => $engines,
            $args = [new EngineZIL130()]
        );

        $this->assertSame($args, $result);
    }

    /**
     * Type of predefined arguments will not be verified
     */
    public function testVariadicTypeIntersectionParameterAndUnnamedArguments(): void
    {
        $result = $this->resolveClosure(
            fn(EngineInterface&MadeInUssrInterface ...$engines) => $engines,
            [[new EngineZIL130(), new EngineVAZ2101(), new stdClass(), new EngineMarkTwo(), new stdClass()]],
            validate: false
        );

        $this->assertCount(5, $result);
    }
}
