<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Internal\Factory;

use PHPUnit\Framework\TestCase;
use Spiral\Core\BinderInterface;
use Spiral\Core\Config;
use Spiral\Core\FactoryInterface;
use Spiral\Core\Internal\Factory;
use Spiral\Core\Internal\Registry;
use Spiral\Core\Internal\State;

abstract class BaseTest extends TestCase
{
    protected Registry $constructor;
    protected Config $config;

    protected function setUp(): void
    {
        $this->config = new Config();
        $this->constructor = new Registry($this->config, [
            'state' => new State(),
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

    protected function make(string $class, array $args = [], string $context = null): mixed
    {
        return $this->createFactory()->make($class, $args, $context);
    }

    protected function createFactory(): FactoryInterface
    {
        return new Factory($this->constructor);
    }
}
