<?php

declare(strict_types=1);

namespace Spiral\Tests\Events\Processor;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Spiral\Events\Config\EventListener;
use Spiral\Events\Config\EventsConfig;
use Spiral\Events\AutowireListenerFactory;
use Spiral\Events\ListenerRegistryInterface;
use Spiral\Events\Processor\ConfigProcessor;
use Spiral\Tests\Events\Fixtures\Event\BarEvent;
use Spiral\Tests\Events\Fixtures\Event\FooEvent;
use Spiral\Tests\Events\Fixtures\Listener\ClassAndMethodAttribute;
use Spiral\Tests\Events\Fixtures\Listener\ClassAttribute;

final class ConfigProcessorTest extends TestCase
{
    public static function listenersDataProvider(): \Traversable
    {
        yield [
            [
                FooEvent::class => [new EventListener(ClassAndMethodAttribute::class, 'onFooEvent', 1)],
            ],
            [
                FooEvent::class,
                (new AutowireListenerFactory())->create(ClassAndMethodAttribute::class, 'onFooEvent'),
                1,
            ],
        ];
        yield [
            [
                BarEvent::class => [new EventListener(ClassAndMethodAttribute::class, 'onBarEvent', 1)],
            ],
            [
                BarEvent::class,
                (new AutowireListenerFactory())->create(ClassAndMethodAttribute::class, 'onBarEvent'),
                1,
            ],
        ];
        yield [
            [BarEvent::class => [ClassAttribute::class]],
            [
                BarEvent::class,
                (new AutowireListenerFactory())->create(ClassAttribute::class, '__invoke'),
                0,
            ],
        ];
    }

    #[DataProvider('listenersDataProvider')]
    public function testProcess(array $listener, array $args): void
    {
        $registry = new class implements ListenerRegistryInterface {
            public string $event;
            public \Closure $listener;
            public int $priority;

            public function addListener(string $event, callable $listener, int $priority = 0): void
            {
                $this->event = $event;
                $this->listener = $listener;
                $this->priority = $priority;
            }
        };

        $processor = new ConfigProcessor(
            new EventsConfig(['listeners' => $listener]),
            new AutowireListenerFactory(),
            $registry,
        );
        $processor->process();

        self::assertSame($args[0], $registry->event);
        self::assertEquals($args[1], $registry->listener);
        self::assertSame($args[2], $registry->priority);
    }
}
