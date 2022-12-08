<?php

declare(strict_types=1);

namespace Spiral\Core;

use Psr\Container\ContainerInterface;
use ReflectionFunctionAbstract as ContextFunction;
use Spiral\Core\Container\Autowire;
use Spiral\Core\Container\InjectableInterface;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Core\Exception\Container\ContainerException;
use Spiral\Core\Exception\LogicException;
use Spiral\Core\Internal\DestructorTrait;

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
 * @psalm-import-type TResolver from BinderInterface
 * @psalm-import-type TInvokable from InvokerInterface
 * @psalm-suppress PropertyNotSetInConstructor
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
    private ResolverInterface|Internal\Resolver $resolver;
    private FactoryInterface|Internal\Factory $factory;
    private ContainerInterface|Internal\Container $container;
    private BinderInterface|Internal\Binder $binder;
    private InvokerInterface|Internal\Invoker $invoker;

    /**
     * Container constructor.
     */
    public function __construct(Config $config = new Config())
    {
        $constructor = new Internal\Registry($config, [
            'state' => new Internal\State(),
        ]);
        foreach ($config as $property => $class) {
            if (\property_exists($this, $property)) {
                $this->$property = $constructor->get($property, $class);
            }
        }

        /** @psalm-suppress PossiblyNullPropertyAssignment */
        $this->state->bindings = [
            self::class               => \WeakReference::create($this),
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
    ): array {
        return $this->resolver->resolveArguments($reflection, $parameters, $validate);
    }

    public function validateArguments(ContextFunction $reflection, array $arguments = []): void
    {
        $this->resolver->validateArguments($reflection, $arguments);
    }

    /**
     * @param string|null $context Related to parameter caused injection if any.
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
     *
     * @psalm-suppress PossiblyInvalidArgument, PossiblyInvalidCast
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
                : $scope($this);
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
     */
    public function bind(string $alias, string|array|callable|object $resolver): void
    {
        $this->binder->bind($alias, $resolver);
    }

    /**
     * Bind value resolver to container alias to be executed as cached. Resolver can be class name
     * (will be constructed only once), function array or Closure (executed only once call).
     *
     * @psalm-param TResolver $resolver
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
     * @psalm-param TInvokable $target
     */
    public function invoke(mixed $target, array $parameters = []): mixed
    {
        return $this->invoker->invoke($target, $parameters);
    }

    /**
     * Bind class or class interface to the injector source (InjectorInterface).
     */
    public function bindInjector(string $class, string $injector): void
    {
        $this->binder->bindInjector($class, $injector);
    }

    public function removeInjector(string $class): void
    {
        $this->binder->removeInjector($class);
    }

    public function hasInjector(string $class): bool
    {
        return $this->binder->hasInjector($class);
    }
}
