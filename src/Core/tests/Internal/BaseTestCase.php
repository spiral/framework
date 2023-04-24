<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Internal;

use PHPUnit\Framework\TestCase;
use Spiral\Core\BinderInterface;
use Spiral\Core\Config;
use Spiral\Core\Container\InjectorInterface;
use Spiral\Core\FactoryInterface;
use Spiral\Core\Internal\Common\Registry;
use Spiral\Core\Internal\Factory;
use Spiral\Core\Internal\Resolver;
use Spiral\Core\Internal\Scope;
use Spiral\Core\Internal\State;
use Spiral\Core\ResolverInterface;

abstract class BaseTestCase extends TestCase
{
    protected Registry $constructor;
    protected Config $config;

    protected function setUp(): void
    {
        $this->config = new Config();
        $this->constructor = new Registry($this->config, [
            'state' => new State(),
            'scope' => new Scope(),
        ]);
        parent::setUp();
    }

    protected function bind(string $alias, string|array|callable|object $resolver): void
    {
        $binder = $this->constructor->get('binder', BinderInterface::class);
        \assert($binder instanceof BinderInterface);
        $binder->bind($alias, $resolver);
    }

    protected function bindSingleton(string $alias, string|array|callable|object $resolver): void
    {
        $binder = $this->constructor->get('binder', BinderInterface::class);
        \assert($binder instanceof BinderInterface);
        $binder->bindSingleton($alias, $resolver);
    }

    /**
     * @template TClass
     *
     * @param class-string<TClass> $class
     * @param class-string<InjectorInterface<TClass>> $injector
     */
    protected function bindInjector(string $class, string $injector): void
    {
        $binder = $this->constructor->get('binder', BinderInterface::class);
        \assert($binder instanceof BinderInterface);
        $binder->bindInjector($class, $injector);
    }

    protected function createResolver(): ResolverInterface
    {
        return new Resolver($this->constructor);
    }

    protected function createFactory(): FactoryInterface
    {
        return new Factory($this->constructor);
    }
}
