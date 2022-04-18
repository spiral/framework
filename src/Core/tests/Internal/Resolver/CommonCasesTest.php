<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Internal\Resolver;

use Spiral\Tests\Core\Stub\EngineInterface;
use Spiral\Tests\Core\Stub\EngineMarkTwo;

final class CommonCasesTest extends BaseTest
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
}
