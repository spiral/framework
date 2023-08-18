<?php

declare(strict_types=1);

namespace Spiral\Core;

use Psr\Container\ContainerInterface;
use ReflectionFunctionAbstract as ContextFunction;
use Spiral\Core\Config\Alias;
use Spiral\Core\Config\WeakReference;
use Spiral\Core\Container\Autowire;
use Spiral\Core\Container\InjectableInterface;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Core\Exception\Container\ContainerException;
use Spiral\Core\Exception\LogicException;
use Spiral\Core\Exception\Scope\FinalizersException;
use Spiral\Core\Exception\Scope\ScopeContainerLeakedException;
use Spiral\Core\Internal\Common\DestructorTrait;
use Spiral\Core\Internal\Config\StateBinder;

/**
 * Auto-wiring container: declarative singletons, contextual injections, parent container
 * delegation and ability to lazy wire.
 *
 * Container does not support setter injections, private properties, etc. Normally it will work
 * with classes only to be as much invisible as possible. Attention, this is hungry implementation
 * of container, meaning it WILL try to resolve dependency unless you specified custom lazy
 * factory.
 *
 * You can use injectors to delegate class resolution to external container.
 *
 * @see InjectableInterface
 * @see SingletonInterface
 *
 * @psalm-import-type TResolver from BinderInterface
 * @psalm-import-type TInvokable from InvokerInterface
 */
final class Container implements
    ContainerInterface,
    BinderInterface,
    FactoryInterface,
    ResolverInterface,
    InvokerInterface,
    ContainerScopeInterface,
    ScopeInterface
{
    use DestructorTrait;

    public const DEFAULT_ROOT_SCOPE_NAME = 'root';

    private Internal\State $state;
    private ResolverInterface|Internal\Resolver $resolver;
    private FactoryInterface|Internal\Factory $factory;
    private ContainerInterface|Internal\Container $container;
    private BinderInterface|Internal\Binder $binder;
    private InvokerInterface|Internal\Invoker $invoker;
    private Internal\Scope $scope;

    /**
     * Container constructor.
     */
    public function __construct(
        private Config $config = new Config(),
        ?string $scopeName = self::DEFAULT_ROOT_SCOPE_NAME,
    ) {
        $this->initServices($this, $scopeName);

        /** @psalm-suppress RedundantPropertyInitializationCheck */
        \assert(isset($this->state));

        // Bind himself
        $shared = new Alias(self::class);
        $this->state->bindings = \array_merge($this->state->bindings, [
            self::class => new WeakReference(\WeakReference::create($this)),
            ContainerInterface::class => $shared,
            BinderInterface::class => $shared,
            FactoryInterface::class => $shared,
            ContainerScopeInterface::class => $shared,
            ScopeInterface::class => $shared,
            ResolverInterface::class => $shared,
            InvokerInterface::class => $shared,
        ]);
    }

    public function __destruct()
    {
        $this->closeScope();
    }

    /**
     * Container can not be cloned.
     */
    public function __clone()
    {
        throw new LogicException('Container is not cloneable.');
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
     *
     * @throws ContainerException
     * @throws \Throwable
     */
    public function make(string $alias, array $parameters = [], string $context = null): mixed
    {
        /** @psalm-suppress TooManyArguments */
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
     * @return ($id is class-string ? T : mixed)
     *
     * @throws ContainerException
     * @throws \Throwable
     */
    public function get(string|Autowire $id, string $context = null): mixed
    {
        /** @psalm-suppress TooManyArguments */
        return $this->container->get($id, $context);
    }

    public function has(string $id): bool
    {
        return $this->container->has($id);
    }

    public function getBinder(?string $scope = null): BinderInterface
    {
        return $scope === null
            ? $this->binder
            : new StateBinder($this->config->scopedBindings->getState($scope));
    }

    /**
     * @throws \Throwable
     *
     * @deprecated use {@see runScoped()} instead.
     */
    public function runScope(array $bindings, callable $scope): mixed
    {
        $binds = &$this->state->bindings;
        $singletons = &$this->state->singletons;
        $cleanup = $previous = $prevSin = [];
        foreach ($bindings as $alias => $resolver) {
            // Store previous bindings
            if (isset($binds[$alias])) {
                $previous[$alias] = $binds[$alias];
            } else {
                // Store bindings to be removed
                $cleanup[] = $alias;
            }
            // Store previous singletons
            if (isset($singletons[$alias])) {
                $prevSin[$alias] = $singletons[$alias];
                unset($singletons[$alias]);
            }

            $this->binder->bind($alias, $resolver);
        }

        try {
            return ContainerScope::getContainer() !== $this
                ? ContainerScope::runScope($this, $scope)
                : $scope($this);
        } finally {
            // Remove new bindings
            foreach ($cleanup as $alias) {
                unset($binds[$alias], $singletons[$alias]);
            }
            // Restore previous bindings
            foreach ($previous as $alias => $resolver) {
                $binds[$alias] = $resolver;
            }
            // Restore singletons
            foreach ($prevSin as $alias => $instance) {
                $singletons[$alias] = $instance;
            }
        }
    }

    /**
     * Invoke given closure or function withing specific IoC scope.
     */
    public function runScoped(callable $closure, array $bindings = [], ?string $name = null, bool $autowire = true): mixed
    {
        // Open scope
        $container = new self($this->config, $name);

        try {
            // Configure scope
            $container->scope->setParent($this, $this->scope);

            // Add specific bindings
            foreach ($bindings as $alias => $resolver) {
                $container->binder->bind($alias, $resolver);
            }

            return ContainerScope::runScope(
                $container,
                static function (self $container) use ($autowire, $closure): mixed {
                    try {
                        return $autowire
                            ? $container->invoke($closure)
                            : $closure($container);
                    } finally {
                        $container->closeScope();
                    }
                }
            );
        } finally {
            // Check the container has not been leaked
            $link = \WeakReference::create($container);
            unset($container);
            if ($link->get() !== null) {
                throw new ScopeContainerLeakedException($name, $this->scope->getParentScopeNames());
            }
        }
    }

    /**
     * Get current scope container.
     *
     * @internal it might be removed in the future.
     */
    public function getCurrentContainer(): ContainerInterface
    {
        return ContainerScope::getContainer() ?? $this;
    }

    /**
     * Bind value resolver to container alias. Resolver can be class name (will be constructed
     * for each method call), function array or Closure (executed every call). Only object resolvers
     * supported by this method.
     */
    public function bind(string $alias, mixed $resolver): void
    {
        $this->binder->bind($alias, $resolver);
    }

    /**
     * Bind value resolver to container alias to be executed as cached. Resolver can be class name
     * (will be constructed only once), function array or Closure (executed only once call).
     *
     * @psalm-param TResolver $resolver
     * @param bool $force If the value is false, an exception will be thrown when attempting
     *  to bind an already constructed singleton.
     */
    public function bindSingleton(string $alias, string|array|callable|object $resolver, bool $force = true): void
    {
        if ($force) {
            $this->binder->removeBinding($alias);
        }

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

    /**
     * Init internal container services.
     */
    private function initServices(
        self $container,
        ?string $scopeName,
    ): void {
        $isRoot = $container->config->lockRoot();

        // Get named scope or create anonymous one
        $state = match (true) {
            $scopeName === null => new Internal\State(),
            // Only root container can make default bindings directly
            $isRoot => $container->config->scopedBindings->getState($scopeName),
            default => clone $container->config->scopedBindings->getState($scopeName),
        };

        $constructor = new Internal\Common\Registry($container->config, [
            'state' => $state,
            'scope' => new Internal\Scope($scopeName),
        ]);

        // Create container services
        foreach ($container->config as $property => $class) {
            if (\property_exists($container, $property)) {
                $container->$property = $constructor->get($property, $class);
            }
        }
    }

    /**
     * Execute finalizers and destruct the container.
     *
     * @throws FinalizersException
     */
    private function closeScope(): void
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset($this->scope)) {
            $this->destruct();
            return;
        }

        $scopeName = $this->scope->getScopeName();

        // Run finalizers
        $errors = [];
        foreach ($this->state->finalizers as $finalizer) {
            try {
                $this->invoker->invoke($finalizer);
            } catch (\Throwable $e) {
                $errors[] = $e;
            }
        }

        // Destroy the container
        $this->destruct();

        // Throw collected errors
        if ($errors !== []) {
            throw new FinalizersException($scopeName, $errors);
        }
    }
}
