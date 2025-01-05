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

        self::assertSame('foo', $dto->listener);
        self::assertSame('__invoke', $dto->method);
        self::assertSame(0, $dto->priority);
    }

    public function testMethod(): void
    {
        $dto = new EventListener('foo', method: 'bar');

        self::assertSame('foo', $dto->listener);
        self::assertSame('bar', $dto->method);
        self::assertSame(0, $dto->priority);
    }

    public function testPriority(): void
    {
        $dto = new EventListener('foo', method: 'bar', priority: 10);

        self::assertSame('foo', $dto->listener);
        self::assertSame('bar', $dto->method);
        self::assertSame(10, $dto->priority);
    }
}
