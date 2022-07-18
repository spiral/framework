<?php

declare(strict_types=1);

namespace Spiral\Core\Internal;

use Psr\Container\ContainerInterface;
use Spiral\Core\BinderInterface;
use Spiral\Core\Container\Autowire;
use Spiral\Core\Container\InjectableInterface;
use Spiral\Core\Container\InjectorInterface;
use Spiral\Core\Exception\Container\ContainerException;

/**
 * @psalm-type TResolver = class-string|non-empty-string|callable|array{class-string, non-empty-string}
 *
 * @internal
 */
final class Binder implements BinderInterface
{
    use DestructorTrait;

    private State $state;
    private ContainerInterface $container;

    public function __construct(Registry $constructor)
    {
        $constructor->set('binder', $this);

        $this->state = $constructor->get('state', State::class);
        $this->container = $constructor->get('container', ContainerInterface::class);
    }

    /**
     * Bind value resolver to container alias. Resolver can be class name (will be constructed
     * for each method call), function array or Closure (executed every call). Only object resolvers
     * supported by this method.
     *
     * @psalm-param TResolver|object $resolver
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
     * Bind value resolver to container alias to be executed as cached. Resolver can be class name
     * (will be constructed only once), function array or Closure (executed only once call).
     *
     * @psalm-param TResolver|object $resolver
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

    /**
     * Check if alias points to constructed instance (singleton).
     */
    public function hasInstance(string $alias): bool
    {
        if (!$this->container->has($alias)) {
            return false;
        }
        $bindings = &$this->state->bindings;

        while (isset($bindings[$alias]) && \is_string($bindings[$alias])) {
            //Checking alias tree
            $alias = $bindings[$alias];
        }

        return isset($bindings[$alias]) && \is_object($bindings[$alias]);
    }

    public function removeBinding(string $alias): void
    {
        unset($this->state->bindings[$alias]);
    }

    /**
     * Bind class or class interface to the injector source (InjectorInterface).
     *
     * @template TClass
     *
     * @param class-string<TClass> $class
     * @param class-string<InjectorInterface<TClass>> $injector
     */
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
