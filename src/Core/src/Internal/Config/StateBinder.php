<?php

declare(strict_types=1);

namespace Spiral\Core\Internal\Config;

use Exception;
use InvalidArgumentException;
use Spiral\Core\BinderInterface;
use Spiral\Core\Config\Alias;
use Spiral\Core\Config\Binding;
use Spiral\Core\Config\Factory;
use Spiral\Core\Config\Inflector;
use Spiral\Core\Config\Injectable;
use Spiral\Core\Config\Scalar;
use Spiral\Core\Config\Shared;
use Spiral\Core\Config\DeferredFactory;
use Spiral\Core\Container\Autowire;
use Spiral\Core\Container\InjectableInterface;
use Spiral\Core\Exception\Binder\SingletonOverloadException;
use Spiral\Core\Exception\ConfiguratorException;
use Spiral\Core\Exception\Container\ContainerException;
use Spiral\Core\Internal\State;
use Throwable;
use WeakReference;

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
    public function bind(string $alias, mixed $resolver): void
    {
        if ($resolver instanceof Inflector && (\interface_exists($alias) || \class_exists($alias))) {
            $this->state->inflectors[$alias][] = $resolver;
            return;
        }

        try {
            $config = $this->makeConfig($resolver, false);
        } catch (\Throwable $e) {
            throw $this->invalidBindingException($alias, $e);
        }

        $this->setBinding($alias, $config);
    }

    /**
     * @param TResolver|object $resolver
     */
    public function bindSingleton(string $alias, mixed $resolver): void
    {
        try {
            $config = $this->makeConfig($resolver, true);
        } catch (\Throwable $e) {
            throw $this->invalidBindingException($alias, $e);
        }

        $this->setBinding($alias, $config);
    }

    public function hasInstance(string $alias): bool
    {
        $bindings = &$this->state->bindings;

        $flags = [];
        while ($binding = $bindings[$alias] ?? null and $binding::class === Alias::class) {
            //Checking alias tree
            if ($flags[$binding->alias] ?? false) {
                return $binding->alias === $alias ?: throw new Exception('Circular alias detected');
            }

            if (\array_key_exists($alias, $this->state->singletons)) {
                return true;
            }

            $flags[$binding->alias] = true;
            $alias = $binding->alias;
        }

        return \array_key_exists($alias, $this->state->singletons) or isset($bindings[$alias]);
    }

    public function removeBinding(string $alias): void
    {
        unset($this->state->bindings[$alias], $this->state->singletons[$alias]);
    }

    public function bindInjector(string $class, string $injector): void
    {
        $this->state->bindings[$class] = new Injectable($injector);
        $this->state->injectors[$class] = $injector;
    }

    public function removeInjector(string $class): void
    {
        unset($this->state->injectors[$class]);
        if (!isset($this->state->bindings[$class]) || $this->state->bindings[$class]::class !== Injectable::class) {
            return;
        }
        unset($this->state->bindings[$class]);
    }

    public function hasInjector(string $class): bool
    {
        if (\array_key_exists($class, $this->state->injectors)) {
            return true;
        }

        try {
            $reflection = new \ReflectionClass($class);
        } catch (\ReflectionException $e) {
            throw new ContainerException($e->getMessage(), $e->getCode(), $e);
        }

        if (
            $reflection->implementsInterface(InjectableInterface::class)
            && $reflection->hasConstant('INJECTOR')
        ) {
            $const = $reflection->getConstant('INJECTOR');
            $this->bindInjector($class, $const);

            return true;
        }

        // check interfaces
        foreach ($this->state->injectors as $target => $injector) {
            if (
                (\class_exists($target, true) && $reflection->isSubclassOf($target))
                ||
                (\interface_exists($target, true) && $reflection->implementsInterface($target))
            ) {
                $this->state->bindings[$class] = new Injectable($injector);

                return true;
            }
        }

        return false;
    }

    private function makeConfig(mixed $resolver, bool $singleton): Binding
    {
        return match (true) {
            $resolver instanceof Binding => $resolver,
            $resolver instanceof \Closure => new Factory($resolver, $singleton),
            $resolver instanceof Autowire => new \Spiral\Core\Config\Autowire($resolver, $singleton),
            $resolver instanceof WeakReference => new \Spiral\Core\Config\WeakReference($resolver),
            \is_string($resolver) => new Alias($resolver, $singleton),
            \is_scalar($resolver) => new Scalar($resolver),
            \is_object($resolver) => new Shared($resolver),
            \is_array($resolver) => $this->makeConfigFromArray($resolver, $singleton),
            default => throw new InvalidArgumentException('Unknown resolver type.'),
        };
    }

    private function makeConfigFromArray(array $resolver, bool $singleton): Binding
    {
        if (\is_callable($resolver)) {
            return new Factory($resolver, $singleton);
        }

        // Validate lazy invokable array
        if (!isset($resolver[0]) || !isset($resolver[1]) || !\is_string($resolver[1]) || $resolver[1] === '') {
            throw new InvalidArgumentException('Incompatible array declaration for resolver.');
        }
        if ((!\is_string($resolver[0]) && !\is_object($resolver[0])) || $resolver[0] === '') {
            throw new InvalidArgumentException('Incompatible array declaration for resolver.');
        }

        return new DeferredFactory($resolver, $singleton);
    }

    private function invalidBindingException(string $alias, Throwable $previous): Throwable
    {
        return new ConfiguratorException(\sprintf(
            'Invalid binding for `%s`. %s',
            $alias,
            $previous->getMessage(),
        ), previous: $previous);
    }

    private function setBinding(string $alias, Binding $config): void
    {
        if (isset($this->state->singletons[$alias])) {
            throw new SingletonOverloadException($alias);
        }

        $this->state->bindings[$alias] = $config;
    }
}
