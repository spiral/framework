<?php

declare(strict_types=1);

namespace Spiral\Tests\Events\Processor;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;
use Spiral\Events\Attribute\Listener;
use Spiral\Events\AutowireListenerFactory;
use Spiral\Events\ListenerLocatorInterface;
use Spiral\Events\ListenerRegistryInterface;
use Spiral\Events\Processor\AttributeProcessor;
use Spiral\Tests\Events\Fixtures\Event\BarEvent;
use Spiral\Tests\Events\Fixtures\Event\FooEvent;
use Spiral\Tests\Events\Fixtures\Listener\ClassAndMethodAttribute;
use Spiral\Tests\Events\Fixtures\Listener\ClassAttribute;
use Spiral\Tests\Events\Fixtures\Listener\ClassAttributeWithParameters;
use Spiral\Tests\Events\Fixtures\Listener\MethodAttribute;
use Spiral\Tests\Events\Fixtures\Listener\MethodAttributeWithParameters;

final class AttributeProcessorTest extends TestCase
{
    /**
     * @dataProvider listenersDataProvider
     */
    public function testProcess(string $class, Listener $listener, array $args): void
    {
        $locator = new class($class, $listener) implements ListenerLocatorInterface {
            public function __construct(
                private readonly string $class,
                private readonly Listener $listener
            ) {
            }

            public function findListeners(): \Generator
            {
                yield $this->class => $this->listener;
            }
        };

        $registry = new class() implements ListenerRegistryInterface {

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

        $processor = new AttributeProcessor($locator, new AutowireListenerFactory(), $registry);
        $processor->process();

        $this->assertSame($args[0], $registry->event);
        $this->assertEquals($args[1], $registry->listener);
        $this->assertSame($args[2], $registry->priority);
    }

    public function listenersDataProvider(): \Traversable
    {
        yield [
            ClassAndMethodAttribute::class,
            new Listener(method: 'onFooEvent'),
            [
                FooEvent::class,
                (new AutowireListenerFactory(new Container()))->create(ClassAndMethodAttribute::class, 'onFooEvent'),
                0
            ]
        ];
        yield [
            ClassAndMethodAttribute::class,
            new Listener(method: 'onBarEvent'),
            [
                BarEvent::class,
                (new AutowireListenerFactory(new Container()))->create(ClassAndMethodAttribute::class, 'onBarEvent'),
                0
            ]
        ];
        yield [
            ClassAttribute::class,
            new Listener(),
            [
                BarEvent::class,
                (new AutowireListenerFactory(new Container()))->create(ClassAttribute::class, '__invoke'),
                0
            ]
        ];
        yield [
            ClassAttributeWithParameters::class,
            new Listener(method: 'customMethod'),
            [
                FooEvent::class,
                (new AutowireListenerFactory(new Container()))->create(ClassAttributeWithParameters::class, 'customMethod'),
                0
            ]
        ];
        yield [
            MethodAttribute::class,
            new Listener(method: '__invoke'),
            [
                BarEvent::class,
                (new AutowireListenerFactory(new Container()))->create(MethodAttribute::class, '__invoke'),
                0
            ]
        ];
        yield [
            MethodAttributeWithParameters::class,
            new Listener(method: 'customMethod'),
            [
                FooEvent::class,
                (new AutowireListenerFactory(new Container()))->create(MethodAttributeWithParameters::class, 'customMethod'),
                0
            ]
        ];
    }
}
