<?php

declare(strict_types=1);

namespace Spiral\Core;

use Psr\Container\ContainerInterface;
use ReflectionFunctionAbstract as ContextFunction;
use Spiral\Core\Container\Autowire;
use Spiral\Core\Container\InjectableInterface;
use Spiral\Core\Container\InjectorInterface;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Core\Exception\Container\ContainerException;
use Spiral\Core\Exception\LogicException;
use Spiral\Core\Internal\DestructorTrait;
use WeakReference;

/**
 * Auto-wiring container: declarative singletons, contextual injections, parent container
 * delegation and ability to lazy wire.
 *
 * Container does not support setter injections, private properties and etc. Normally it will work
 * with classes only to be as much invisible as possible. Attention, this is hungry implementation
 * of container, meaning it WILL try to resolve dependency unless you specified custom lazy
 * factory.
 *
 * You can use injectors to delegate class resolution to external container.
 *
 * @see \Spiral\Core\Container::registerInstance() to add your own behaviours.
 *
 * @see InjectableInterface
 * @see SingletonInterface
 *
 * @psalm-type TResolver = class-string|non-empty-string|callable|array{class-string, non-empty-string}
 */
final class Container implements
    ContainerInterface,
    BinderInterface,
    FactoryInterface,
    ResolverInterface,
    InvokerInterface,
    ScopeInterface
{
    use DestructorTrait;

    private Internal\State $state;
    private Internal\Resolver $resolver;
    private Internal\Factory $factory;
    private ContainerInterface $container;
    private BinderInterface $binder;
    private InvokerInterface $invoker;

    /**
     * Container constructor.
     */
    public function __construct(Config $config = new Config())
    {
        $constructor = new Internal\Constructor($config, [
            'state' => new Internal\State(),
        ]);
        foreach ($config as $property => $class) {
            $this->$property = $constructor->get($property, $class);
        }

        $this->state->bindings = [
            self::class               => WeakReference::create($this),
            ContainerInterface::class => self::class,
            BinderInterface::class    => self::class,
            FactoryInterface::class   => self::class,
            ScopeInterface::class     => self::class,
            ResolverInterface::class  => self::class,
            InvokerInterface::class   => self::class,
        ];
    }

    public function __destruct()
    {
        $this->destruct();
    }

    /**
     * Container can not be cloned.
     */
    public function __clone()
    {
        throw new LogicException('Container is not clonable.');
    }

    public function resolveArguments(
        ContextFunction $reflection,
        array $parameters = [],
        bool $validate = true,
        bool $strict = true
    ): array {
        return $this->resolver->resolveArguments($reflection, $parameters, $validate, $strict);
    }

    public function validateArguments(ContextFunction $reflection, array $arguments = [], bool $strict = true): void
    {
        $this->resolver->validateArguments($reflection, $arguments, $strict);
    }

    /**
     * @template T
     *
     * @param class-string<T> $alias
     * @param string|null $context Related to parameter caused injection if any.
     *
     * @return T
     *
     * @throws \Throwable
     */
    public function make(string $alias, array $parameters = [], string $context = null): mixed
    {
        return $this->factory->make($alias, $parameters, $context);
    }

    /**
     * Context parameter will be passed to class injectors, which makes possible to use this method
     * as:
     *
     * $this->container->get(DatabaseInterface::class, 'default');
     *
     * Attention, context ignored when outer container has instance by alias.
     *
     * @template T
     *
     * @param class-string<T>|string|Autowire $id
     * @param string|null $context Call context.
     *
     * @return T
     * @psalm-return ($id is class-string ? T : mixed)
     *
     * @throws ContainerException
     * @throws \Throwable
     */
    public function get(string|Autowire $id, string $context = null): mixed
    {
        return $this->container->get($id, $context);
    }

    public function has(string $id): bool
    {
        return $this->container->has($id);
    }

    public function runScope(array $bindings, callable $scope): mixed
    {
        $binds = &$this->state->bindings;
        $cleanup = $previous = [];
        foreach ($bindings as $alias => $resolver) {
            if (isset($binds[$alias])) {
                $previous[$alias] = $binds[$alias];
            } else {
                $cleanup[] = $alias;
            }

            $this->binder->bind($alias, $resolver);
        }

        try {
            return ContainerScope::getContainer() !== $this
                ? ContainerScope::runScope($this, $scope)
                : $scope();
        } finally {
            foreach ($previous as $alias => $resolver) {
                $binds[$alias] = $resolver;
            }

            foreach ($cleanup as $alias) {
                unset($binds[$alias]);
            }
        }
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
        $this->binder->bind($alias, $resolver);
    }

    /**
     * Bind value resolver to container alias to be executed as cached. Resolver can be class name
     * (will be constructed only once), function array or Closure (executed only once call).
     *
     * @psalm-param TResolver|object $resolver
     */
    public function bindSingleton(string $alias, string|array|callable|object $resolver): void
    {
        $this->binder->bindSingleton($alias, $resolver);
    }

    /**
     * Check if alias points to constructed instance (singleton).
     */
    public function hasInstance(string $alias): bool
    {
        return $this->binder->hasInstance($alias);
    }

    public function removeBinding(string $alias): void
    {
        $this->binder->removeBinding($alias);
    }

    /**
     * @psalm-param TResolver $target
     */
    public function invoke(mixed $target, array $parameters = []): mixed
    {
        return $this->invoker->invoke($target, $parameters);
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
        $this->binder->bindInjector($class, $injector);
    }

    public function removeInjector(string $class): void
    {
        $this->binder->removeInjector($class);
    }

    /**
     * Every declared Container binding. Must not be used in production code due container format is
     * vary.
     */
    public function getBindings(): array
    {
        return $this->state->bindings;
    }

    /**
     * Every binded injector.
     */
    public function getInjectors(): array
    {
        return $this->state->injectors;
    }
}
