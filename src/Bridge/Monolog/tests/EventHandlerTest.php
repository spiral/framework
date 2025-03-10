<?php

declare(strict_types=1);

namespace Spiral\Tests\Monolog;

use PHPUnit\Framework\TestCase;
use Spiral\Logger\Event\LogEvent;
use Spiral\Logger\ListenerRegistry;
use Spiral\Monolog\EventHandler;

final class EventHandlerTest extends TestCase
{
    private ListenerRegistry $registry;
    private object $listener;

    public function testHandle(): void
    {
        $handler = new EventHandler($this->registry);

        $result = $handler->handle([
            'datetime' => new \DateTimeImmutable(),
            'channel' => 'foo',
            'level' => 100,
            'message' => 'bar',
            'context' => ['foo' => 'bar'],
        ]);

        self::assertSame('foo', $this->listener->event->getChannel());
        self::assertSame('bar', $this->listener->event->getMessage());
        self::assertSame(['foo' => 'bar'], $this->listener->event->getContext());
        self::assertSame('debug', $this->listener->event->getLevel());
        self::assertFalse($result);
    }

    public function testHandleWithBubbleFalse(): void
    {
        $handler = new EventHandler(listenerRegistry: $this->registry, bubble: false);

        $result = $handler->handle([
            'datetime' => new \DateTimeImmutable(),
            'channel' => 'foo',
            'level' => 100,
            'message' => 'bar',
            'context' => ['foo' => 'bar'],
        ]);

        self::assertSame('foo', $this->listener->event->getChannel());
        self::assertSame('bar', $this->listener->event->getMessage());
        self::assertSame(['foo' => 'bar'], $this->listener->event->getContext());
        self::assertSame('debug', $this->listener->event->getLevel());
        self::assertTrue($result);
    }

    protected function setUp(): void
    {
        $this->listener = new class {
            public LogEvent $event;

            public function __invoke(LogEvent $event): void
            {
                $this->event = $event;
            }
        };

        $this->registry = new ListenerRegistry();
        $this->registry->addListener($this->listener);
    }
}
