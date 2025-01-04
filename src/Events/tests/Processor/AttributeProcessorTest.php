<?php

declare(strict_types=1);

namespace Spiral\Tests\Events\Processor;

use Mockery as m;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Spiral\Attributes\ReaderInterface;
use Spiral\Events\Attribute\Listener;
use Spiral\Events\AutowireListenerFactory;
use Spiral\Events\ListenerFactoryInterface;
use Spiral\Events\ListenerRegistryInterface;
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
use Spiral\Tokenizer\TokenizerListenerRegistryInterface;

final class AttributeProcessorTest extends TestCase
{
    use m\Adapter\Phpunit\MockeryPHPUnitIntegration;

    public function testEventListenerShouldNotBeRegisteredWithoutListenerRegistry(): void
    {
        $tokenizerRegistry = m::mock(TokenizerListenerRegistryInterface::class);
        $reader = m::mock(ReaderInterface::class);
        $factory = m::mock(ListenerFactoryInterface::class);

        $tokenizerRegistry->shouldNotReceive('addListener');

        $processor = new AttributeProcessor($tokenizerRegistry, $reader, $factory);
        $processor->process();
    }

    public function testEventListenerShouldNotBeRegisteredWithListenerRegistry(): void
    {
        $tokenizerRegistry = m::mock(TokenizerListenerRegistryInterface::class);
        $reader = m::mock(ReaderInterface::class);
        $factory = m::mock(ListenerFactoryInterface::class);
        $listenerRegistry = m::mock(ListenerRegistryInterface::class);

        $tokenizerRegistry->shouldReceive('addListener')
            ->once()
            ->withArgs(fn (AttributeProcessor $attributeProcessor): bool => true);

        new AttributeProcessor($tokenizerRegistry, $reader, $factory, $listenerRegistry);
    }

    public function testEventListenerShouldThrowAnExceptionWhenListenerNotFinalized(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Tokenizer did not finalize Spiral\Events\Processor\AttributeProcessor listener.');

        $tokenizerRegistry = m::mock(TokenizerListenerRegistryInterface::class);
        $reader = m::mock(ReaderInterface::class);
        $factory = m::mock(ListenerFactoryInterface::class);
        $listenerRegistry = m::mock(ListenerRegistryInterface::class);

        $tokenizerRegistry->shouldReceive('addListener')->once();

        $processor = new AttributeProcessor($tokenizerRegistry, $reader, $factory, $listenerRegistry);
        $processor->process();
    }

    /**
     * @param class-string $class
     */
    #[DataProvider('listenersDataProvider')]
    public function testProcess(string $class, Listener $listener, array $args, int $listeners = 1): void
    {
        $tokenizerRegistry = m::mock(TokenizerListenerRegistryInterface::class);
        $reader = m::mock(ReaderInterface::class);
        $factory = m::mock(ListenerFactoryInterface::class);
        $registry = $this->createListenerRegistry();

        $tokenizerRegistry->shouldReceive('addListener')->once();

        $ref = new \ReflectionClass($class);
        $reader->shouldReceive('getClassMetadata')->once()->with($ref, Listener::class)->andReturn([$listener]);
        $reader->shouldReceive('getFunctionMetadata');
        $factory->shouldReceive('create');

        $processor = new AttributeProcessor($tokenizerRegistry, $reader, $factory, $registry);
        $processor->listen($ref);
        $processor->finalize();

        $processor->process();

        self::assertSame((array)$args[0], $registry->events);
        self::assertEquals($args[1], $registry->listener);
        self::assertSame($args[2], $registry->priority);
        self::assertSame($listeners, $registry->listeners);
    }

    public static function listenersDataProvider(): \Traversable
    {
        yield [
            ClassAndMethodAttribute::class,
            new Listener(method: 'onFooEvent'),
            [
                FooEvent::class,
                (new AutowireListenerFactory())->create(ClassAndMethodAttribute::class, 'onFooEvent'),
                0,
            ],
        ];
        yield [
            ClassAttributeUnionType::class,
            new Listener(method: '__invoke'),
            [
                [FooEvent::class, BarEvent::class],
                (new AutowireListenerFactory())->create(ClassAttributeUnionType::class, '__invoke'),
                0,
            ],
            2,
        ];
        yield [
            ClassAndMethodAttribute::class,
            new Listener(method: 'onBarEvent'),
            [
                BarEvent::class,
                (new AutowireListenerFactory())->create(ClassAndMethodAttribute::class, 'onBarEvent'),
                0,
            ],
        ];
        yield [
            ClassAttribute::class,
            new Listener(),
            [
                BarEvent::class,
                (new AutowireListenerFactory())->create(ClassAttribute::class, '__invoke'),
                0,
            ],
        ];
        yield [
            ClassAttributeWithParameters::class,
            new Listener(method: 'customMethod'),
            [
                FooEvent::class,
                (new AutowireListenerFactory())->create(ClassAttributeWithParameters::class, 'customMethod'),
                0,
            ],
        ];
        yield [
            MethodAttribute::class,
            new Listener(method: '__invoke'),
            [
                BarEvent::class,
                (new AutowireListenerFactory())->create(MethodAttribute::class, '__invoke'),
                0,
            ],
        ];
        yield [
            MethodAttributeWithParameters::class,
            new Listener(method: 'customMethod'),
            [
                FooEvent::class,
                (new AutowireListenerFactory())->create(MethodAttributeWithParameters::class, 'customMethod'),
                0,
            ],
        ];
    }

    public function createListenerRegistry(): PlainListenerRegistry
    {
        return new PlainListenerRegistry();
    }
}
