<?php

declare(strict_types=1);

namespace Spiral\Tests\Events\Config;

use PHPUnit\Framework\TestCase;
use Spiral\Events\Config\EventListener;

final class EventListenerTest extends TestCase
{
    public function testDefaultMethod(): void
    {
        $dto = new EventListener('foo');

        $this->assertSame('foo', $dto->listener);
        $this->assertSame('__invoke', $dto->method);
        $this->assertSame(0, $dto->priority);
    }

    public function testMethod(): void
    {
        $dto = new EventListener('foo', method: 'bar');

        $this->assertSame('foo', $dto->listener);
        $this->assertSame('bar', $dto->method);
        $this->assertSame(0, $dto->priority);
    }

    public function testPriority(): void
    {
        $dto = new EventListener('foo', method: 'bar', priority: 10);

        $this->assertSame('foo', $dto->listener);
        $this->assertSame('bar', $dto->method);
        $this->assertSame(10, $dto->priority);
    }
}
