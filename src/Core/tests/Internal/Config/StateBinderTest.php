<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Internal\Config;

use Spiral\Core\Attribute\Singleton;
use Spiral\Core\BinderInterface;
use Spiral\Core\Exception\Binder\SingletonOverloadException;
use Spiral\Core\FactoryInterface;
use Spiral\Tests\Core\Fixtures\SampleClass;
use Spiral\Tests\Core\Internal\BaseTestCase;

final class StateBinderTest extends BaseTestCase
{
    public function testOverrideBindSingletonException(): void
    {
        $binder = $this->constructor->get('binder', BinderInterface::class);
        $factory = $this->constructor->get('factory', FactoryInterface::class);

        $binder->bind('singleton', new \stdClass());
        $binder->bindSingleton('test', 'singleton');

        $factory->make('test');

        $this->expectException(SingletonOverloadException::class);
        $binder->bindSingleton('test', new \stdClass());
    }

    public function testOverrideBindException(): void
    {
        $binder = $this->constructor->get('binder', BinderInterface::class);
        $factory = $this->constructor->get('factory', FactoryInterface::class);

        $binder->bind('singleton', new \stdClass());
        $binder->bindSingleton('test', 'singleton');

        $factory->make('test');

        $this->expectException(SingletonOverloadException::class);
        $binder->bind('test', new \stdClass());
    }

    public function testHasInstanceAfterMakeWithoutAlias(): void
    {
        $binder = $this->constructor->get('binder', BinderInterface::class);
        $factory = $this->constructor->get('factory', FactoryInterface::class);

        $this->bindSingleton('test', new #[Singleton] class {});
        $factory->make('test');

        $this->assertTrue($binder->hasInstance('test'));
    }

    public function testHasInstanceAfterMakeWithAlias(): void
    {
        $binder = $this->constructor->get('binder', BinderInterface::class);
        $factory = $this->constructor->get('factory', FactoryInterface::class);

        $this->bindSingleton('test', SampleClass::class);
        $factory->make('test');

        $this->assertTrue($binder->hasInstance('test'));
    }

    public function testHasInstanceAfterMakeWithNestedAlias(): void
    {
        $binder = $this->constructor->get('binder', BinderInterface::class);
        $factory = $this->constructor->get('factory', FactoryInterface::class);

        $this->bindSingleton('sampleClass', SampleClass::class);
        $this->bindSingleton('foo', 'sampleClass');

        $this->bindSingleton('bar', 'foo');
        $factory->make('bar');

        $this->assertTrue($binder->hasInstance('bar'));
    }

    public function testHasBindingWithoutBinding(): void
    {
        $binder = $this->constructor->get('binder', BinderInterface::class);

        $this->assertFalse($binder->hasBinding('bar'));
    }

    public function testHasBindingWithBinding(): void
    {
        $binder = $this->constructor->get('binder', BinderInterface::class);

        $this->bindSingleton('bar', 'foo');

        $this->assertTrue($binder->hasBinding('bar'));
    }
}
