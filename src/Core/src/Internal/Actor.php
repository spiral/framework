<?php

declare(strict_types=1);

namespace Spiral\Core\Internal;

use Psr\Container\ContainerInterface;
use ReflectionFunctionAbstract as ContextFunction;
use Spiral\Core\BinderInterface;
use Spiral\Core\Config\Alias;
use Spiral\Core\Config\Binding;
use Spiral\Core\Attribute;
use Spiral\Core\Container\InjectorInterface;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Core\Exception\Container\AutowireException;
use Spiral\Core\Exception\Container\ContainerException;
use Spiral\Core\Exception\Container\InjectionException;
use Spiral\Core\Exception\Container\NotCallableException;
use Spiral\Core\Exception\Container\NotFoundException;
use Spiral\Core\Exception\Container\RecursiveProxyException;
use Spiral\Core\Exception\Resolver\ValidationException;
use Spiral\Core\Exception\Resolver\WrongTypeException;
use Spiral\Core\Exception\Scope\BadScopeException;
use Spiral\Core\FactoryInterface;
use Spiral\Core\Internal\Common\DestructorTrait;
use Spiral\Core\Internal\Common\Registry;
use Spiral\Core\Internal\Factory\Ctx;
use Spiral\Core\Internal\Proxy\RetryContext;
use Spiral\Core\InvokerInterface;
use Spiral\Core\Options;
use Spiral\Core\ResolverInterface;
use Spiral\Core\Config;

/**
 * @internal
 */
final class Actor
{
    use DestructorTrait;

    private State $state;
    private BinderInterface $binder;
    private InvokerInterface $invoker;
    private ContainerInterface $container;
    private ResolverInterface $resolver;
    private FactoryInterface $factory;
    private Tracer $tracer;
    private Scope $scope;
    private Options $options;

    public function __construct(Registry $constructor)
    {
        $constructor->set('hub', $this);

        $this->state = $constructor->get('state', State::class);
        $this->binder = $constructor->get('binder', BinderInterface::class);
        $this->invoker = $constructor->get('invoker', InvokerInterface::class);
        $this->container = $constructor->get('container', ContainerInterface::class);
        $this->resolver = $constructor->get('resolver', ResolverInterface::class);
        $this->factory = $constructor->get('factory', FactoryInterface::class);
        $this->tracer = $constructor->get('tracer', Tracer::class);
        $this->scope = $constructor->get('scope', Scope::class);
        $this->options = $constructor->getOptions();
    }

    /**
     * Get class name of the resolving object.
     * With it, you can quickly get cached singleton or detect that there are injector or binding.
     * The method does not detect that the class is instantiable.
     *
     * @param non-empty-string $alias
     *
     * @param self|null $static Will be set to the hub where the result was found.
     *
     * @return class-string|null Returns {@see null} if exactly one returning class cannot be resolved.
     * @psalm-suppress all
     */
    public function resolveType(
        string $alias,
        ?Binding &$binding = null,
        ?object $singleton = null,
        ?object $injector = null,
        ?self $static = null,
    ): ?string {
        // Aliases to prevent circular dependencies
        $as = [];
        $static = $this;
        do {
            $bindings = &$static->state->bindings;
            $singletons = &$static->state->singletons;
            $injectors = &$static->state->injectors;
            // $scope = $static->scope;
            if (\array_key_exists($alias, $singletons)) {
                $singleton = $singletons[$alias];
                $binding = $bindings[$alias] ?? null;
                $injector = $injectors[$alias] ?? null;
                return \is_object($singleton::class) ? $singleton::class : null;
            }

            if (\array_key_exists($alias, $bindings)) {
                $b = $bindings[$alias];
                if ($b::class === Alias::class) {
                    $alias = $b->alias;
                    if (\array_key_exists($alias, $as)) {
                        // A cycle detected
                        // todo Exception?
                        return null;
                    }
                    $as[$alias] = true;
                    continue;
                }

                $binding = $b;
                return $binding->getReturnClass();
            }

            if (\array_key_exists($alias, $injectors)) {
                $injector = $injectors[$alias];
                $binding = $bindings[$alias] ?? null;
                return $alias;
            }

            // Go to parent scope
            $static = $static->scope->getParentActor();
        } while ($static !== null);

        return \class_exists($alias) ? $alias : null;
    }

    public function resolveBinding(
        object $binding,
        string $alias,
        \Stringable|string|null $context,
        array $arguments,
    ): mixed {
        return match ($binding::class) {
            Config\Alias::class => $this->resolveAlias($binding, $alias, $context, $arguments),
            Config\Proxy::class,
            Config\DeprecationProxy::class => $this->resolveProxy($binding, $alias, $context),
            Config\Autowire::class => $this->resolveAutowire($binding, $alias, $context, $arguments),
            Config\DeferredFactory::class,
            Config\Factory::class => $this->resolveFactory($binding, $alias, $context, $arguments),
            Config\Shared::class => $this->resolveShared($binding, $alias, $context, $arguments),
            Config\Injectable::class => $this->resolveInjector(
                $binding,
                new Ctx(alias: $alias, class: $alias, context: $context),
                $arguments,
            ),
            Config\Scalar::class => $binding->value,
            Config\WeakReference::class => $this
                ->resolveWeakReference($binding, $alias, $context, $arguments),
            default => $binding,
        };
    }

    /**
     * Automatically create class.
     * Object will be cached if the $arguments list is empty.
     *
     * @psalm-assert class-string $class
     *
     * @throws AutowireException
     * @throws \Throwable
     */
    public function autowire(Ctx $ctx, array $arguments): object
    {
        // $this;
        if (!(\class_exists($ctx->class) || (
                \interface_exists($ctx->class)
                &&
                (isset($this->state->injectors[$ctx->class]) || $this->binder->hasInjector($ctx->class))
            ))
        ) {
            throw new NotFoundException($this->tracer->combineTraceMessage(\sprintf(
                'Can\'t resolve `%s`: undefined class or binding `%s`.',
                $this->tracer->getRootAlias(),
                $ctx->class,
            )));
        }

        // automatically create instance
        $instance = $this->createInstance($ctx, $arguments);

        // apply registration functions to created instance
        return $arguments === []
            ? $this->registerInstance($ctx, $instance)
            : $instance;
    }

    /**
     * @psalm-suppress UnusedParam
     * todo wat should we do with $arguments?
     */
    private function resolveInjector(Config\Injectable $binding, Ctx $ctx, array $arguments)
    {
        $context = $ctx->context;
        try {
            $reflection = $ctx->reflection ??= new \ReflectionClass($ctx->class);
        } catch (\ReflectionException $e) {
            throw new ContainerException($e->getMessage(), $e->getCode(), $e);
        }

        $injector = $binding->injector;

        try {
            $injectorInstance = \is_object($injector) ? $injector : $this->container->get($injector);

            if (!$injectorInstance instanceof InjectorInterface) {
                throw new InjectionException(
                    \sprintf(
                        "Class '%s' must be an instance of InjectorInterface for '%s'.",
                        $injectorInstance::class,
                        $reflection->getName(),
                    ),
                );
            }

            /** @var array<class-string<InjectorInterface>, \ReflectionMethod|false> $cache reflection for extended injectors */
            static $cache = [];
            $extended = $cache[$injectorInstance::class] ??= (
            static fn(\ReflectionType $type): bool =>
                $type::class === \ReflectionUnionType::class || (string) $type === 'mixed'
            )(
                ($refMethod = new \ReflectionMethod($injectorInstance, 'createInjection'))
                    ->getParameters()[1]->getType()
            ) ? $refMethod : false;

            $asIs = $extended && (\is_string($context) || $this->validateArguments($extended, [$reflection, $context]));
            $instance = $injectorInstance->createInjection($reflection, match (true) {
                $asIs => $context,
                $context instanceof \ReflectionParameter => $context->getName(),
                default => (string) $context,
            });

            if (!$reflection->isInstance($instance)) {
                throw new InjectionException(
                    \sprintf(
                        "Invalid injection response for '%s'.",
                        $reflection->getName(),
                    ),
                );
            }

            return $instance;
        } finally {
            $this->state->bindings[$ctx->class] ??= $binding;
        }
    }

    private function resolveAlias(
        Config\Alias $binding,
        string $alias,
        \Stringable|string|null $context,
        array $arguments,
    ): mixed {
        $result = $binding->alias === $alias
            ? $this->autowire(
                new Ctx(alias: $alias, class: $binding->alias, context: $context, singleton: $binding->singleton),
                $arguments,
            )
            //Binding is pointing to something else
            : $this->factory->make($binding->alias, $arguments, $context);

        if ($binding->singleton && $arguments === []) {
            $this->state->singletons[$alias] = $result;
        }

        return $result;
    }

    private function resolveProxy(Config\Proxy $binding, string $alias, \Stringable|string|null $context): mixed
    {
        if ($context instanceof RetryContext) {
            return $binding->fallbackFactory === null
                ? throw new RecursiveProxyException(
                    $alias,
                    $this->scope->getScopeName(),
                )
                : ($binding->fallbackFactory)($this->container, $context->context);
        }

        $result = Proxy::create(new \ReflectionClass($binding->getReturnClass()), $context, new Attribute\Proxy());

        if ($binding->singleton) {
            $this->state->singletons[$alias] = $result;
        }

        return $result;
    }

    private function resolveShared(
        Config\Shared $binding,
        string $alias,
        \Stringable|string|null $context,
        array $arguments,
    ): object {
        $avoidCache = $arguments !== [];

        if ($avoidCache) {
            return $this->createInstance(
                new Ctx(alias: $alias, class: $binding->value::class, context: $context),
                $arguments,
            );
        }

        if ($binding->singleton) {
            $this->state->singletons[$alias] = $binding->value;
        }

        return $binding->value;
    }

    private function resolveAutowire(
        Config\Autowire $binding,
        string $alias,
        \Stringable|string|null $context,
        array $arguments,
    ): mixed {
        $target = $binding->autowire->alias;
        $ctx = new Ctx(alias: $alias, class: $target, context: $context, singleton: $binding->singleton);

        $instance = $alias === $target
            ? $this->autowire($ctx, \array_merge($binding->autowire->parameters, $arguments))
            : $binding->autowire->resolve($this->factory, $arguments);

        return $this->validateNewInstance($instance, $ctx, $arguments);
    }

    private function resolveFactory(
        Config\Factory|Config\DeferredFactory $binding,
        string $alias,
        \Stringable|string|null $context,
        array $arguments,
    ): mixed {
        $ctx = new Ctx(alias: $alias, class: $alias, context: $context, singleton: $binding->singleton);
        try {
            $instance = $binding::class === Config\Factory::class && $binding->getParametersCount() === 0
                ? ($binding->factory)()
                : $this->invoker->invoke($binding->factory, $arguments);
        } catch (NotCallableException $e) {
            throw new ContainerException(
                $this->tracer->combineTraceMessage(\sprintf('Invalid binding for `%s`.', $ctx->alias)),
                $e->getCode(),
                $e,
            );
        }

        return \is_object($instance) ? $this->validateNewInstance($instance, $ctx, $arguments) : $instance;
    }

    private function resolveWeakReference(
        Config\WeakReference $binding,
        string $alias,
        \Stringable|string|null $context,
        array $arguments,
    ): ?object {
        $avoidCache = $arguments !== [];

        if (($avoidCache || $binding->reference->get() === null) && \class_exists($alias)) {
            try {
                $this->tracer->push(false, alias: $alias, source: \WeakReference::class, context: $context);

                $object = $this->createInstance(
                    new Ctx(alias: $alias, class: $alias, context: $context),
                    $arguments,
                );
                if ($avoidCache) {
                    return $object;
                }
                $binding->reference = \WeakReference::create($object);
            } catch (\Throwable) {
                throw new ContainerException(
                    $this->tracer->combineTraceMessage(
                        \sprintf(
                            'Can\'t resolve `%s`: can\'t instantiate `%s` from WeakReference binding.',
                            $this->tracer->getRootAlias(),
                            $alias,
                        ),
                    ),
                );
            } finally {
                $this->tracer->pop();
            }
        }

        return $binding->reference->get();
    }

    /**
     * @throws BadScopeException
     * @throws \Throwable
     */
    private function validateNewInstance(
        object $instance,
        Ctx $ctx,
        array $arguments,
    ): object {
        if ($this->options->checkScope) {
            // Check scope name
            $ctx->reflection = new \ReflectionClass($instance);
            $scopeName = ($ctx->reflection->getAttributes(Attribute\Scope::class)[0] ?? null)?->newInstance()->name;
            if ($scopeName !== null) {
                $scope = $this->scope;
                while ($scope->getScopeName() !== $scopeName) {
                    $scope = $scope->getParentScope() ?? throw new BadScopeException($scopeName, $instance::class);
                }
            }
        }

        return $arguments === []
            ? $this->registerInstance($ctx, $instance)
            : $instance;
    }

    /**
     * Create instance of desired class.
     *
     * @template TObject of object
     *
     * @param Ctx<TObject> $ctx
     * @param array $arguments Constructor arguments.
     *
     * @return TObject
     *
     * @throws ContainerException
     * @throws \Throwable
     */
    private function createInstance(
        Ctx $ctx,
        array $arguments,
    ): object {
        $class = $ctx->class;
        try {
            $ctx->reflection = $reflection = new \ReflectionClass($class);
        } catch (\ReflectionException $e) {
            throw new ContainerException($e->getMessage(), $e->getCode(), $e);
        }

        // Check scope name
        if ($this->options->checkScope) {
            $scope = ($reflection->getAttributes(Attribute\Scope::class)[0] ?? null)?->newInstance()->name;
            if ($scope !== null && $scope !== $this->scope->getScopeName()) {
                throw new BadScopeException($scope, $class);
            }
        }

        // We have to construct class using external injector when we know the exact context
        if ($arguments === [] && $this->binder->hasInjector($class)) {
            return $this->resolveInjector($this->state->bindings[$ctx->class], $ctx, $arguments);
        }

        if (!$reflection->isInstantiable()) {
            $itIs = match (true) {
                $reflection->isEnum() => 'Enum',
                $reflection->isAbstract() => 'Abstract class',
                default => 'Class',
            };
            throw new ContainerException(
                $this->tracer->combineTraceMessage(\sprintf('%s `%s` can not be constructed.', $itIs, $class)),
            );
        }

        $constructor = $reflection->getConstructor();

        if ($constructor !== null) {
            try {
                $this->tracer->push(false, action: 'resolve arguments', signature: $constructor);
                $this->tracer->push(true);
                $x = $this->options->validateArguments;
                $args = $this->resolver->resolveArguments($constructor, $arguments, $this->options->validateArguments);
            } catch (ValidationException $e) {
                throw new ContainerException(
                    $this->tracer->combineTraceMessage(
                        \sprintf(
                            'Can\'t resolve `%s`. %s',
                            $this->tracer->getRootAlias(),
                            $e->getMessage(),
                        ),
                    ),
                );
            } finally {
                $this->tracer->pop(true);
                $this->tracer->pop(false);
            }
            try {
                // Using constructor with resolved arguments
                $this->tracer->push(false, call: "$class::__construct", arguments: $args);
                $this->tracer->push(true);
                $instance = new $class(...$args);
            } catch (\TypeError $e) {
                throw new WrongTypeException($constructor, $e);
            } finally {
                $this->tracer->pop(true);
                $this->tracer->pop(false);
            }
        } else {
            // No constructor specified
            $instance = $reflection->newInstance();
        }

        return $instance;
    }

    /**
     * Register instance in container, might perform methods like auto-singletons, log populations, etc.
     */
    private function registerInstance(Ctx $ctx, object $instance): object
    {
        $ctx->reflection ??= new \ReflectionClass($instance);

        $instance = $this->runInflector($instance);

        //Declarative singletons
        if ($this->isSingleton($ctx)) {
            $this->state->singletons[$ctx->alias] = $instance;
        }

        // Register finalizer
        $finalizer = $this->getFinalizer($ctx, $instance);
        if ($finalizer !== null) {
            $this->state->finalizers[] = $finalizer;
        }

        return $instance;
    }

    /**
     * Check the class was configured as a singleton.
     */
    private function isSingleton(Ctx $ctx): bool
    {
        if ($ctx->singleton === true) {
            return true;
        }

        /** @psalm-suppress RedundantCondition https://github.com/vimeo/psalm/issues/9489 */
        if ($ctx->reflection->implementsInterface(SingletonInterface::class)) {
            return true;
        }

        return $ctx->reflection->getAttributes(Attribute\Singleton::class) !== [];
    }

    private function getFinalizer(Ctx $ctx, object $instance): ?callable
    {
        /**
         * @psalm-suppress UnnecessaryVarAnnotation
         * @var Attribute\Finalize|null $attribute
         */
        $attribute = ($ctx->reflection->getAttributes(Attribute\Finalize::class)[0] ?? null)?->newInstance();
        if ($attribute === null) {
            return null;
        }

        return [$instance, $attribute->method];
    }

    /**
     * Find and run inflector
     */
    private function runInflector(object $instance): object
    {
        $scope = $this->scope;

        while ($scope !== null) {
            foreach ($this->state->inflectors as $class => $inflectors) {
                if ($instance instanceof $class) {
                    foreach ($inflectors as $inflector) {
                        $instance = $inflector->getParametersCount() > 1
                            ? $this->invoker->invoke($inflector->inflector, [$instance])
                            : ($inflector->inflector)($instance);
                    }
                }
            }

            $scope = $scope->getParentScope();
        }

        return $instance;
    }

    private function validateArguments(ContextFunction $reflection, array $arguments = []): bool
    {
        try {
            $this->resolver->validateArguments($reflection, $arguments);
        } catch (\Throwable) {
            return false;
        }

        return true;
    }
}
