<?php

declare(strict_types=1);

namespace Spiral\Tests\Events\Processor;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;
use Spiral\Events\Attribute\Listener;
use Spiral\Events\AutowireListenerFactory;
use Spiral\Events\ListenerLocatorInterface;
use Spiral\Events\Processor\AttributeProcessor;
use Spiral\Tests\Events\Fixtures\Event\BarEvent;
use Spiral\Tests\Events\Fixtures\Event\FooEvent;
use Spiral\Tests\Events\Fixtures\Listener\ClassAndMethodAttribute;
use Spiral\Tests\Events\Fixtures\Listener\ClassAttribute;
use Spiral\Tests\Events\Fixtures\Listener\ClassAttributeUnionType;
use Spiral\Tests\Events\Fixtures\Listener\ClassAttributeWithParameters;
use Spiral\Tests\Events\Fixtures\Listener\MethodAttribute;
use Spiral\Tests\Events\Fixtures\Listener\MethodAttributeWithParameters;
use Spiral\Tests\Events\Stub\PlainListenerRegistry;

final class AttributeProcessorTest extends TestCase
{
    /**
     * @param class-string $class
     *
     * @dataProvider listenersDataProvider
     */
    public function testProcess(string $class, Listener $listener, array $args): void
    {
        $locator = $this->createListenerLocator($class, $listener);
        $registry = $this->createListenerRegistry();

        $processor = new AttributeProcessor($locator, new AutowireListenerFactory(), $registry);
        $processor->process();

        $this->assertSame((array)$args[0], $registry->events);
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
                (new AutowireListenerFactory())->create(ClassAndMethodAttribute::class, 'onFooEvent'),
                0
            ]
        ];
        yield [
            ClassAttributeUnionType::class,
            new Listener(method: '__invoke'),
            [
                [FooEvent::class, BarEvent::class],
                (new AutowireListenerFactory())->create(ClassAttributeUnionType::class, '__invoke'),
                0
            ]
        ];
        yield [
            ClassAndMethodAttribute::class,
            new Listener(method: 'onBarEvent'),
            [
                BarEvent::class,
                (new AutowireListenerFactory())->create(ClassAndMethodAttribute::class, 'onBarEvent'),
                0
            ]
        ];
        yield [
            ClassAttribute::class,
            new Listener(),
            [
                BarEvent::class,
                (new AutowireListenerFactory())->create(ClassAttribute::class, '__invoke'),
                0
            ]
        ];
        yield [
            ClassAttributeWithParameters::class,
            new Listener(method: 'customMethod'),
            [
                FooEvent::class,
                (new AutowireListenerFactory())->create(ClassAttributeWithParameters::class, 'customMethod'),
                0
            ]
        ];
        yield [
            MethodAttribute::class,
            new Listener(method: '__invoke'),
            [
                BarEvent::class,
                (new AutowireListenerFactory())->create(MethodAttribute::class, '__invoke'),
                0
            ]
        ];
        yield [
            MethodAttributeWithParameters::class,
            new Listener(method: 'customMethod'),
            [
                FooEvent::class,
                (new AutowireListenerFactory())->create(MethodAttributeWithParameters::class, 'customMethod'),
                0
            ]
        ];
    }

    /**
     * @param class-string $class
     */
    public function createListenerLocator(string $class, Listener $listener): ListenerLocatorInterface
    {
        return new class($class, $listener) implements ListenerLocatorInterface {
            /**
             * @param class-string $class
             */
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
    }

    public function createListenerRegistry(): PlainListenerRegistry
    {
        return new PlainListenerRegistry();
    }
}
