<?php

declare(strict_types=1);

namespace Spiral\Core\Internal;

use Psr\Container\ContainerExceptionInterface;
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
use Spiral\Core\Exception\Container\TracedContainerException;
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
        $this->scope = $constructor->get('scope', Scope::class);
        $this->options = $constructor->getOptions();
    }

    public function disableBinding(string $alias): void
    {
        unset($this->state->bindings[$alias]);
    }

    public function enableBinding(string $alias, Binding $binding): void
    {
        $this->state->bindings[$alias] ??= $binding;
    }

    /**
     * Get class name of the resolving object.
     * With it, you can quickly get cached singleton or detect that there are injector or binding.
     * The method does not detect that the class is instantiable.
     *
     * @param non-empty-string $alias
     *
     * @param self|null $actor Will be set to the hub where the result was found.
     *
     * @return class-string|null Returns {@see null} if exactly one returning class cannot be resolved.
     * @psalm-suppress all
     */
    public function resolveType(
        string $alias,
        ?Binding &$binding = null,
        ?object &$singleton = null,
        ?object &$injector = null,
        ?self &$actor = null,
        bool $followAlias = true,
    ): ?string {
        // Aliases to prevent circular dependencies
        $as = [];
        $actor = $this;
        do {
            $bindings = &$actor->state->bindings;
            $singletons = &$actor->state->singletons;
            $injectors = &$actor->state->injectors;
            $binding = $bindings[$alias] ?? null;
            if (\array_key_exists($alias, $singletons)) {
                $singleton = $singletons[$alias];
                $injector = $injectors[$alias] ?? null;
                return \is_object($singleton::class) ? $singleton::class : null;
            }

            if ($binding !== null) {
                if ($followAlias && $binding::class === Alias::class) {
                    if ($binding->alias === $alias) {
                        break;
                    }

                    $alias = $binding->alias;
                    \array_key_exists($alias, $as) and throw new ContainerException(
                        \sprintf('Circular dependency detected for alias `%s`.', $alias),
                    );
                    $as[$alias] = true;
                    continue;
                }

                return $binding->getReturnClass();
            }

            if (\array_key_exists($alias, $injectors)) {
                $injector = $injectors[$alias];
                $binding = $bindings[$alias] ?? null;
                return $alias;
            }

            // Go to parent scope
            $parent = $actor->scope->getParentActor();
            if ($parent === null) {
                break;
            }

            $actor = $parent;
        } while (true);

        return \class_exists($alias) ? $alias : null;
    }

    public function resolveBinding(
        object $binding,
        string $alias,
        \Stringable|string|null $context,
        array $arguments,
        Tracer $tracer,
    ): mixed {
        return match ($binding::class) {
            Config\Alias::class => $this->resolveAlias($binding, $alias, $context, $arguments, $tracer),
            Config\Proxy::class,
            Config\DeprecationProxy::class => $this->resolveProxy($binding, $alias, $context),
            Config\Autowire::class => $this->resolveAutowire($binding, $alias, $context, $arguments, $tracer),
            Config\DeferredFactory::class,
            Config\Factory::class => $this->resolveFactory($binding, $alias, $context, $arguments, $tracer),
            Config\Shared::class => $this->resolveShared($binding, $alias, $context, $arguments, $tracer),
            Config\Injectable::class => $this->resolveInjector(
                $binding,
                new Ctx(alias: $alias, class: $alias, context: $context),
                $arguments,
                $tracer,
            ),
            Config\Scalar::class => $binding->value,
            Config\WeakReference::class => $this
                ->resolveWeakReference($binding, $alias, $context, $arguments, $tracer),
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
    public function autowire(Ctx $ctx, array $arguments, ?Actor $fallbackActor, Tracer $tracer): object
    {
        \class_exists($ctx->class)
        or (\interface_exists($ctx->class)
            && (isset($this->state->injectors[$ctx->class]) || $this->binder->hasInjector($ctx->class)))
        or throw NotFoundException::createWithTrace(
            $ctx->alias === $ctx->class
                ? "Can't autowire `$ctx->class`: class or injector not found."
                : "Can't resolve `$ctx->alias`: class or injector `$ctx->class` not found.",
            $tracer->getTraces(),
        );

        // automatically create instance
        return $this->createInstance($ctx, $arguments, $fallbackActor, $tracer);
    }

    /**
     * @psalm-suppress UnusedParam
     * todo wat should we do with $arguments?
     */
    private function resolveInjector(Config\Injectable $binding, Ctx $ctx, array $arguments, Tracer $tracer)
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
        } catch (TracedContainerException $e) {
            throw isset($injectorInstance) ? $e : $e::createWithTrace(\sprintf(
                'Can\'t resolve `%s`.',
                $tracer->getRootAlias(),
            ), $tracer->getTraces(), $e);
        } finally {
            $this->state->bindings[$ctx->class] ??= $binding;
        }
    }

    private function resolveAlias(
        Config\Alias $binding,
        string $alias,
        \Stringable|string|null $context,
        array $arguments,
        Tracer $tracer,
    ): mixed {
        if ($binding->alias === $alias) {
            $instance = $this->autowire(
                new Ctx(alias: $alias, class: $binding->alias, context: $context, singleton: $binding->singleton && $arguments === []),
                $arguments,
                $this,
                $tracer,
            );
        } else {
            try {
                //Binding is pointing to something else
                $instance = $this->factory->make($binding->alias, $arguments, $context);
            } catch (TracedContainerException $e) {
                throw $e::createWithTrace(
                    $alias === $tracer->getRootAlias()
                        ? "Can't resolve `{$alias}`."
                        : "Can't resolve `$alias` with alias `{$binding->alias}`.",
                    $tracer->getTraces(),
                    $e,
                );
            }

            $binding->singleton and $arguments === [] and $this->state->singletons[$alias] = $instance;
        }


        return $instance;
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
        Tracer $tracer,
    ): object {
        if ($arguments !== []) {
            // Avoid singleton cache
            return $this->createInstance(
                new Ctx(alias: $alias, class: $binding->value::class, context: $context, singleton: false),
                $arguments,
                $this,
                $tracer,
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
        Tracer $tracer,
    ): mixed {
        $target = $binding->autowire->alias;
        $ctx = new Ctx(alias: $alias, class: $target, context: $context, singleton: $binding->singleton && $arguments === [] ?: null);

        if ($alias === $target) {
            $instance = $this->autowire($ctx, \array_merge($binding->autowire->parameters, $arguments), $this, $tracer);
        } else {
            $instance = $binding->autowire->resolve($this->factory, $arguments);
            $this->validateConstraint($instance, $ctx);
        }

        return $this->registerInstance($ctx, $instance);
    }

    private function resolveFactory(
        Config\Factory|Config\DeferredFactory $binding,
        string $alias,
        \Stringable|string|null $context,
        array $arguments,
        Tracer $tracer,
    ): mixed {
        $ctx = new Ctx(alias: $alias, class: $alias, context: $context, singleton: $binding->singleton && $arguments === [] ?: null);
        try {
            $instance = $binding::class === Config\Factory::class && $binding->getParametersCount() === 0
                ? ($binding->factory)()
                : $this->invoker->invoke($binding->factory, $arguments);
        } catch (NotCallableException $e) {
            throw TracedContainerException::createWithTrace(
                \sprintf('Invalid callable binding for `%s`.', $ctx->alias),
                $tracer->getTraces(),
                $e,
            );
        } catch (TracedContainerException $e) {
            throw $e::createWithTrace(
                \sprintf("Can't resolve `%s`: factory invocation failed.", $tracer->getRootAlias()),
                $tracer->getTraces(),
                $e,
            );
        } catch (ContainerExceptionInterface $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw NotFoundException::createWithTrace(
                \sprintf("Can't resolve `%s` due to factory invocation error: %s", $tracer->getRootAlias(), $e->getMessage()),
                $tracer->getTraces(),
                $e,
            );
        }

        if (\is_object($instance)) {
            $this->validateConstraint($instance, $ctx);
            return $this->registerInstance($ctx, $instance);
        }

        return $instance;
    }

    private function resolveWeakReference(
        Config\WeakReference $binding,
        string $alias,
        \Stringable|string|null $context,
        array $arguments,
        Tracer $tracer,
    ): ?object {
        $avoidCache = $arguments !== [];

        if (($avoidCache || $binding->reference->get() === null) && \class_exists($alias)) {
            try {
                $tracer->push(false, alias: $alias, source: \WeakReference::class, context: $context);

                $object = $this->createInstance(
                    new Ctx(alias: $alias, class: $alias, context: $context, singleton: false),
                    $arguments,
                    $this,
                    $tracer,
                );
                if ($avoidCache) {
                    return $object;
                }
                $binding->reference = \WeakReference::create($object);
            } catch (\Throwable) {
                throw TracedContainerException::createWithTrace(\sprintf(
                    'Can\'t resolve `%s`: can\'t instantiate `%s` from WeakReference binding.',
                    $tracer->getRootAlias(),
                    $alias,
                ), $tracer->getTraces());
            } finally {
                $tracer->pop();
            }
        }

        return $binding->reference->get();
    }

    /**
     * @throws BadScopeException
     * @throws \Throwable
     */
    private function validateConstraint(
        object $instance,
        Ctx $ctx,
    ): void {
        if ($this->options->checkScope) {
            // Check scope name
            $ctx->reflection ??= new \ReflectionClass($instance);
            $scopeName = ($ctx->reflection->getAttributes(Attribute\Scope::class)[0] ?? null)?->newInstance()->name;
            if ($scopeName !== null) {
                $scope = $this->scope;
                while ($scope->getScopeName() !== $scopeName) {
                    $scope = $scope->getParentScope() ?? throw new BadScopeException($scopeName, $instance::class);
                }
            }
        }
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
        ?Actor $fallbackActor,
        Tracer $tracer,
    ): object {
        $class = $ctx->class;
        try {
            $ctx->reflection = $reflection = new \ReflectionClass($class);
        } catch (\ReflectionException $e) {
            throw new ContainerException($e->getMessage(), $e->getCode(), $e);
        }

        // Check Scope attribute
        $actor = $fallbackActor ?? $this;
        if ($this->options->checkScope) { # todo
            $ar = ($reflection->getAttributes(Attribute\Scope::class)[0] ?? null);
            if ($ar !== null) {
                /** @var Attribute\Scope $attr */
                $attr = $ar->newInstance();
                $scope = $this->scope;
                $actor = $this;
                // Go through all parent scopes
                $needed = $actor;
                while ($attr->name !== $scope->getScopeName()) {
                    $needed = $scope->getParentActor();
                    if ($needed === null) {
                        throw new BadScopeException($attr->name, $class);
                    }

                    $scope = $scope->getParentScope();
                }

                // Scope found
                $actor = $needed;
            }
        } # todo

        // We have to construct class using external injector when we know the exact context
        if ($arguments === [] && $actor->binder->hasInjector($class)) {
            return $actor->resolveInjector($actor->state->bindings[$ctx->class], $ctx, $arguments, $tracer);
        }

        if (!$reflection->isInstantiable()) {
            $itIs = match (true) {
                $reflection->isEnum() => 'Enum',
                $reflection->isAbstract() => 'Abstract class',
                default => 'Class',
            };
            throw TracedContainerException::createWithTrace(
                \sprintf('%s `%s` can not be constructed.', $itIs, $class),
                $tracer->getTraces(),
            );
        }

        $constructor = $reflection->getConstructor();

        if ($constructor !== null) {
            try {
                $newScope = $this !== $actor;
                $debug = [
                    'action' => 'resolve arguments',
                    'alias' => $ctx->class,
                    'signature' => $constructor,
                ];
                $newScope and $debug += [
                    'jump to scope' => $actor->scope->getScopeName(),
                    'from scope' => $this->scope->getScopeName(),
                ];
                $tracer->push($newScope, ...$debug);
                $tracer->push(true);
                $args = $actor->resolver->resolveArguments($constructor, $arguments, $actor->options->validateArguments);
            } catch (\Throwable $e) {
                throw TracedContainerException::createWithTrace(
                    \sprintf(
                        "Can't resolve `%s`.",
                        $tracer->getRootAlias(),
                    ), $tracer->getTraces(), $e
                );
            } finally {
                $tracer->pop($newScope);
                $tracer->pop(false);
            }
            try {
                // Using constructor with resolved arguments
                $tracer->push(false, call: "$class::__construct", arguments: $args);
                $tracer->push(true);
                $instance = new $class(...$args);
            } catch (\TypeError $e) {
                throw new WrongTypeException($constructor, $e);
            } catch (TracedContainerException $e) {
                throw $e::createWithTrace(\sprintf(
                    'Can\'t resolve `%s`: failed constructing `%s`.',
                    $tracer->getRootAlias(),
                    $class,
                ), $tracer->getTraces(), $e);
            } finally {
                $tracer->pop(true);
                $tracer->pop(false);
            }
        } else {
            // No constructor specified
            $instance = $reflection->newInstance();
        }

        return $actor->registerInstance($ctx, $instance);
    }

    /**
     * Register instance in container, might perform methods like auto-singletons, log populations, etc.
     */
    private function registerInstance(Ctx $ctx, object $instance): object
    {
        $ctx->reflection ??= new \ReflectionClass($instance);

        $instance = $this->runInflector($instance);

        // Declarative singletons
        $this->isSingleton($ctx) and $this->state->singletons[$ctx->alias] = $instance;

        // Register finalizer
        $finalizer = $this->getFinalizer($ctx, $instance);
        $finalizer === null or $this->state->finalizers[] = $finalizer;

        return $instance;
    }

    /**
     * Check the class was configured as a singleton.
     */
    private function isSingleton(Ctx $ctx): bool
    {
        if (is_bool($ctx->singleton)) {
            return $ctx->singleton;
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
