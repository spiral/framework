<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Internal\Resolver;

use PHPUnit\Framework\TestCase;
use Spiral\Core\BinderInterface;
use Spiral\Core\Config;
use Spiral\Core\Internal\Constructor;
use Spiral\Core\Internal\Resolver;
use Spiral\Core\Internal\State;
use Spiral\Core\ResolverInterface;

abstract class BaseTest extends TestCase
{
    protected Constructor $constructor;
    protected Config $config;

    protected function setUp(): void
    {
        $this->config = new Config();
        $this->constructor = new Constructor($this->config, [
            'state' => new State(),
        ]);
        parent::setUp();
    }

    protected function bindSingleton(string $alias, string|array|callable|object $resolver): void
    {
        $binder = $this->constructor->get('binder', BinderInterface::class);
        \assert($binder instanceof BinderInterface);
        $binder->bindSingleton($alias, $resolver);
    }

    protected function resolveClassConstructor(string $class, array $args = []): mixed
    {
        $classReflection = new \ReflectionClass($class);
        $reflection = $classReflection->getConstructor();
        return $this->createResolver()->resolveArguments($reflection, $args);
    }

    protected function resolveClosure(\Closure $closure, array $args = []): mixed
    {
        return $this->createResolver()->resolveArguments(new \ReflectionFunction($closure), $args);
    }

    protected function createResolver(): ResolverInterface
    {
        return new Resolver($this->constructor);
    }
}
