<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Internal;

use PHPUnit\Framework\TestCase;
use Spiral\Core\BinderInterface;
use Spiral\Core\Config;
use Spiral\Core\FactoryInterface;
use Spiral\Core\Internal\Factory;
use Spiral\Core\Internal\Registry;
use Spiral\Core\Internal\Resolver;
use Spiral\Core\Internal\State;
use Spiral\Core\ResolverInterface;

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

    protected function createResolver(): ResolverInterface
    {
        return new Resolver($this->constructor);
    }

    protected function createFactory(): FactoryInterface
    {
        return new Factory($this->constructor);
    }
}
