<?php

declare(strict_types=1);

namespace Spiral\Tests\Events;

use BadMethodCallException;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Spiral\Core\ContainerScope;
use Spiral\Core\InvokerInterface;
use Spiral\Events\AutowireListenerFactory;
use Spiral\Tests\Events\Fixtures\Event\FooEvent;
use Spiral\Tests\Events\Fixtures\Listener\ClassAndMethodAttribute;

final class AutowireListenerFactoryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private FooEvent $ev;
    private AutowireListenerFactory $factory;
    private ContainerInterface|MockInterface $container;
    private ClassAndMethodAttribute|MockInterface $listener;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ev = new FooEvent('test');
        $this->factory = new AutowireListenerFactory();
        $this->container = m::mock(ContainerInterface::class);
        $this->listener = m::mock(ClassAndMethodAttribute::class);
    }

    public function testCreateListenerForClass(): void
    {
        $this->container->shouldReceive('get')
            ->once()
            ->with(ClassAndMethodAttribute::class)
            ->andReturn($this->listener);

        $this->listener->shouldReceive('onFooEvent')->once()->with($this->ev);

        ContainerScope::runScope($this->container, function (): void {
            $listener = $this->factory->create(ClassAndMethodAttribute::class, 'onFooEvent');
            $listener($this->ev);
        });
    }

    public function testCreateListenerForObject(): void
    {
        $this->listener->shouldReceive('onFooEvent')->once()->with($this->ev);

        ContainerScope::runScope($this->container, function (): void {
            $listener = $this->factory->create($this->listener, 'onFooEvent');
            $listener($this->ev);
        });
    }

    public function testCreateAutowiredListenerForClass(): void
    {
        $this->container->shouldReceive('get')
            ->once()
            ->with(ClassAndMethodAttribute::class)
            ->andReturn($this->listener);

        $this->container->shouldReceive('get')
            ->once()
            ->with(InvokerInterface::class)
            ->andReturn($invoker = m::mock(InvokerInterface::class));

        $invoker->shouldReceive('invoke')
            ->once()
            ->with([$this->listener, 'onFooEventWithTwoArguments'], ['event' => $this->ev]);

        ContainerScope::runScope($this->container, function (): void {
            $listener = $this->factory->create(ClassAndMethodAttribute::class, 'onFooEventWithTwoArguments');
            $listener($this->ev);
        });
    }

    public function testCreateAutowiredListenerForObject(): void
    {
        $this->container->shouldReceive('get')
            ->once()
            ->with(InvokerInterface::class)
            ->andReturn($invoker = m::mock(InvokerInterface::class));

        $invoker->shouldReceive('invoke')
            ->once()
            ->with([$this->listener, 'onFooEventWithTwoArguments'], ['event' => $this->ev]);

        ContainerScope::runScope($this->container, function (): void {
            $listener = $this->factory->create($this->listener, 'onFooEventWithTwoArguments');
            $listener($this->ev);
        });
    }

    public function testListenerWithoutMethodShouldThrowAnException(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage(
            'Listener `Spiral\Tests\Events\Fixtures\Listener\ClassAndMethodAttribute` does not contain `test` method.'
        );

        ContainerScope::runScope($this->container, function (): void {
            $this->factory->create(ClassAndMethodAttribute::class, 'test');
        });
    }
}
