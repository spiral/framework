<?php

declare(strict_types=1);

namespace Spiral\Core\Internal\Config;

use Spiral\Core\BinderInterface;
use Spiral\Core\Container\Autowire;
use Spiral\Core\Container\InjectableInterface;
use Spiral\Core\Exception\Container\ContainerException;
use Spiral\Core\Internal\State;

/**
 * @psalm-import-type TResolver from BinderInterface
 * @internal
 */
class StateBinder implements BinderInterface
{
    public function __construct(
        protected readonly State $state,
    ) {
    }

    /**
     * @param TResolver|object $resolver
     */
    public function bind(string $alias, string|array|callable|object $resolver): void
    {
        if (\is_array($resolver) || $resolver instanceof \Closure || $resolver instanceof Autowire) {
            // array means = execute me, false = not singleton
            $this->state->bindings[$alias] = [$resolver, false];

            return;
        }

        $this->state->bindings[$alias] = $resolver;
    }

    /**
     * @param TResolver|object $resolver
     */
    public function bindSingleton(string $alias, string|array|callable|object $resolver): void
    {
        if (\is_object($resolver) && !$resolver instanceof \Closure && !$resolver instanceof Autowire) {
            // direct binding to an instance
            $this->state->bindings[$alias] = $resolver;

            return;
        }

        $this->state->bindings[$alias] = [$resolver, true];
    }

    public function hasInstance(string $alias): bool
    {
        $bindings = &$this->state->bindings;

        while (\is_string($bindings[$alias] ?? null)) {
            //Checking alias tree
            $alias = $bindings[$alias];
        }

        return isset($bindings[$alias]) && \is_object($bindings[$alias]);
    }

    public function removeBinding(string $alias): void
    {
        unset($this->state->bindings[$alias]);
    }

    public function bindInjector(string $class, string $injector): void
    {
        $this->state->injectors[$class] = $injector;
    }

    public function removeInjector(string $class): void
    {
        unset($this->state->injectors[$class]);
    }

    public function hasInjector(string $class): bool
    {
        try {
            $reflection = new \ReflectionClass($class);
        } catch (\ReflectionException $e) {
            throw new ContainerException($e->getMessage(), $e->getCode(), $e);
        }

        if (\array_key_exists($class, $this->state->injectors)) {
            return $this->state->injectors[$class] !== null;
        }

        if (
            $reflection->implementsInterface(InjectableInterface::class)
            && $reflection->hasConstant('INJECTOR')
        ) {
            $this->state->injectors[$class] = $reflection->getConstant('INJECTOR');

            return true;
        }

        // check interfaces
        foreach ($this->state->injectors as $target => $injector) {
            if (
                \class_exists($target, true)
                && $reflection->isSubclassOf($target)
            ) {
                $this->state->injectors[$class] = $injector;

                return true;
            }

            if (
                \interface_exists($target, true)
                && $reflection->implementsInterface($target)
            ) {
                $this->state->injectors[$class] = $injector;

                return true;
            }
        }

        return false;
    }
}
