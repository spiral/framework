<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Internal\Config;

use Spiral\Core\BinderInterface;
use Spiral\Core\Exception\Binder\SingletonOverloadException;
use Spiral\Core\FactoryInterface;
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
}
