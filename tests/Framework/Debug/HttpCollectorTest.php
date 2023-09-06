<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Debug;

use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Debug\StateCollector\HttpCollector;
use Psr\Http\Message\ServerRequestInterface;

final class HttpCollectorTest extends TestCase
{
    public function testReset(): void
    {
        $collector = new HttpCollector();
        $collector->process(
            $this->createMock(ServerRequestInterface::class),
            $this->createMock(RequestHandlerInterface::class)
        );
        $collector->reset();

        $this->assertNull((new \ReflectionProperty($collector, 'request'))->getValue($collector));
    }
}
