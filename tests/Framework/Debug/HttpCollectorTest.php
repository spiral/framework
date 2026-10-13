<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Debug;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Debug\StateCollector\HttpCollector;

final class HttpCollectorTest extends TestCase
{
    public function testReset(): void
    {
        $collector = new HttpCollector();
        $collector->process(
            $this->createStub(ServerRequestInterface::class),
            $this->createStub(RequestHandlerInterface::class),
        );
        $collector->reset();

        self::assertNull((new \ReflectionProperty($collector, 'request'))->getValue($collector));
    }
}
