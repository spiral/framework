<?php

declare(strict_types=1);

namespace Spiral\Core\Internal;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Spiral\Core\Attribute\Finalize;
use Spiral\Core\Attribute\Scope as ScopeAttribute;
use Spiral\Core\Attribute\Singleton;
use Spiral\Core\BinderInterface;
use Spiral\Core\Config\Injectable;
use Spiral\Core\Config\DeferredFactory;
use Spiral\Core\Container\Autowire;
use Spiral\Core\Container\InjectorInterface;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Core\Exception\Container\AutowireException;
use Spiral\Core\Exception\Container\ContainerException;
use Spiral\Core\Exception\Container\InjectionException;
use Spiral\Core\Exception\Container\NotCallableException;
use Spiral\Core\Exception\Container\NotFoundException;
use Spiral\Core\Exception\Resolver\ValidationException;
use Spiral\Core\Exception\Resolver\WrongTypeException;
use Spiral\Core\Exception\Scope\BadScopeException;
use Spiral\Core\FactoryInterface;
use Spiral\Core\Internal\Common\DestructorTrait;
use Spiral\Core\Internal\Common\Registry;
use Spiral\Core\Internal\Factory\Ctx;
use Spiral\Core\InvokerInterface;
use Spiral\Core\ResolverInterface;
use WeakReference;

/**
 * @internal
 */
final class Factory implements FactoryInterface
{
    use DestructorTrait;

    private State $state;
    private BinderInterface $binder;
    private InvokerInterface $invoker;
    private ContainerInterface $container;
    private ResolverInterface $resolver;
    private Tracer $tracer;
    private Scope $scope;

    public function __construct(Registry $constructor)
    {
        $constructor->set('factory', $this);

        $this->state = $constructor->get('state', State::class);
        $this->binder = $constructor->get('binder', BinderInterface::class);
        $this->invoker = $constructor->get('invoker', InvokerInterface::class);
        $this->container = $constructor->get('container', ContainerInterface::class);
        $this->resolver = $constructor->get('resolver', ResolverInterface::class);
        $this->tracer = $constructor->get('tracer', Tracer::class);
        $this->scope = $constructor->get('scope', Scope::class);
    }

    /**
     * @param string|null $context Related to parameter caused injection if any.
     *
     * @throws \Throwable
     */
    public function make(string $alias, array $parameters = [], string $context = null): mixed
    {
        if ($parameters === [] && \array_key_exists($alias, $this->state->singletons)) {
            return $this->state->singletons[$alias];
        }

        $binding = $this->state->bindings[$alias] ?? null;

        if ($binding === null) {
            return $this->resolveWithoutBinding($alias, $parameters, $context);
        }

        try {
            $this->tracer->push(
                false,
                action: 'resolve from binding',
                alias: $alias,
                scope: $this->scope->getScopeName(),
                context: $context,
                binding: $binding,
            );
            $this->tracer->push(true);

            unset($this->state->bindings[$alias]);
            return match ($binding::class) {
                \Spiral\Core\Config\Alias::class => $this->resolveAlias($binding, $alias, $context, $parameters),
                \Spiral\Core\Config\Autowire::class => $this->resolveAutowire($binding, $alias, $context, $parameters),
                DeferredFactory::class,
                \Spiral\Core\Config\Factory::class => $this->resolveFactory($binding, $alias, $context, $parameters),
                \Spiral\Core\Config\Shared::class => $this->resolveShared($binding, $alias, $context, $parameters),
                Injectable::class => $this->resolveInjector($binding, $alias, $context, $parameters),
                \Spiral\Core\Config\Scalar::class => $binding->value,
                \Spiral\Core\Config\WeakReference::class => $this
                    ->resolveWeakReference($binding, $alias, $context, $parameters),
                default => $binding,
            };
        } finally {
            $this->state->bindings[$alias] ??= $binding;
            $this->tracer->pop(true);
            $this->tracer->pop(false);
        }
    }

    private function resolveInjector(
        Injectable $binding,
        string $alias,
        ?string $context,
        array $arguments,
    ) {
        $ctx = new Ctx(alias: $alias, class: $alias, parameter: $context);

        // We have to construct class using external injector when we know exact context
        if ($arguments !== []) {
            // todo factory?
        }

        $class = $ctx->class;
        try {
            $ctx->reflection = $reflection = new \ReflectionClass($class);
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
                        $reflection->getName()
                    )
                );
            }

            /**
             * @psalm-suppress RedundantCondition
             */
            $instance = $injectorInstance->createInjection($reflection, $ctx->parameter);
            if (!$reflection->isInstance($instance)) {
                throw new InjectionException(
                    \sprintf(
                        "Invalid injection response for '%s'.",
                        $reflection->getName()
                    )
                );
            }

            return $instance;
        } finally {
            $this->state->bindings[$reflection->getName()] ??= $binding; //new Injector($injector);
        }
    }

    private function resolveAlias(
        \Spiral\Core\Config\Alias $binding,
        string $alias,
        ?string $context,
        array $arguments,
    ): mixed {
        $result = $binding->alias === $alias
            ? $this->autowire(
                new Ctx(alias: $alias, class: $binding->alias, parameter: $context, singleton: $binding->singleton),
                $arguments,
            )
            //Binding is pointing to something else
            : $this->make($binding->alias, $arguments, $context);

        if ($binding->singleton && $arguments === []) {
            $this->state->singletons[$alias] = $result;
        }

        return $result;
    }

    private function resolveShared(
        \Spiral\Core\Config\Shared $binding,
        string $alias,
        ?string $context,
        array $arguments,
    ): object {
        $avoidCache = $arguments !== [];
        return $avoidCache
            ? $this->createInstance(
                new Ctx(alias: $alias, class: $binding->value::class, parameter: $context),
                $arguments,
            )
            : $binding->value;
    }

    private function resolveAutowire(
        \Spiral\Core\Config\Autowire $binding,
        string $alias,
        ?string $context,
        array $arguments,
    ): mixed {
        $instance = $binding->autowire->resolve($this, $arguments);

        $ctx = new Ctx(alias: $alias, class: $alias, parameter: $context, singleton: $binding->singleton);
        return $this->validateNewInstance($instance, $ctx, $arguments);
    }

    private function resolveFactory(
        \Spiral\Core\Config\Factory|DeferredFactory $binding,
        string $alias,
        ?string $context,
        array $arguments,
    ): mixed {
        $ctx = new Ctx(alias: $alias, class: $alias, parameter: $context, singleton: $binding->singleton);
        try {
            $instance = $binding::class === \Spiral\Core\Config\Factory::class && $binding->getParametersCount() === 0
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
        \Spiral\Core\Config\WeakReference $binding,
        string $alias,
        ?string $context,
        array $arguments,
    ): ?object {
        $avoidCache = $arguments !== [];

        if (($avoidCache || $binding->reference->get() === null) && \class_exists($alias)) {
            try {
                $this->tracer->push(false, alias: $alias, source: WeakReference::class, context: $context);

                $object = $this->createInstance(
                    new Ctx(alias: $alias, class: $alias, parameter: $context),
                    $arguments,
                );
                if ($avoidCache) {
                    return $object;
                }
                $binding->reference = WeakReference::create($object);
            } catch (\Throwable) {
                throw new ContainerException(
                    $this->tracer->combineTraceMessage(
                        \sprintf(
                            'Can\'t resolve `%s`: can\'t instantiate `%s` from WeakReference binding.',
                            $this->tracer->getRootAlias(),
                            $alias,
                        )
                    )
                );
            } finally {
                $this->tracer->pop();
            }
        }

        return $binding->reference->get();
    }

    private function resolveWithoutBinding(string $alias, array $parameters = [], string $context = null): mixed
    {
        $parent = $this->scope->getParent();

        if ($parent !== null) {
            try {
                $this->tracer->push(false, ...[
                    'current scope' => $this->scope->getScopeName(),
                    'jump to parent scope' => $this->scope->getParentScope()->getScopeName(),
                ]);
                return $parent->make($alias, $parameters, $context);
            } catch (BadScopeException $e) {
                if ($this->scope->getScopeName() !== $e->getScope()) {
                    throw $e;
                }
            } catch (ContainerExceptionInterface $e) {
                $className = match (true) {
                    $e instanceof NotFoundException => NotFoundException::class,
                    default => ContainerException::class,
                };
                throw new $className($this->tracer->combineTraceMessage(\sprintf(
                    'Can\'t resolve `%s`.',
                    $alias,
                )), previous: $e);
            } finally {
                $this->tracer->pop(false);
            }
        }

        $this->tracer->push(false, action: 'autowire', alias: $alias, context: $context);
        try {
            //No direct instructions how to construct class, make is automatically
            return $this->autowire(
                new Ctx(alias: $alias, class: $alias, parameter: $context),
                $parameters,
            );
        } finally {
            $this->tracer->pop(false);
        }
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
    private function autowire(Ctx $ctx, array $arguments): object
    {
        /** @psalm-suppress NoValue, InvalidArrayOffset */
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
     * @throws BadScopeException
     * @throws \Throwable
     */
    private function validateNewInstance(
        object $instance,
        Ctx $ctx,
        array $arguments,
    ): object {
        // Check scope name
        $ctx->reflection = new \ReflectionClass($instance);
        $scopeName = ($ctx->reflection->getAttributes(ScopeAttribute::class)[0] ?? null)?->newInstance()->name;
        if ($scopeName !== null && $scopeName !== $this->scope->getScopeName()) {
            throw new BadScopeException($scopeName, $instance::class);
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
     * @param array $parameters Constructor parameters.
     *
     * @return TObject
     *
     * @throws ContainerException
     * @throws \Throwable
     */
    private function createInstance(
        Ctx $ctx,
        array $parameters,
    ): object {
        $class = $ctx->class;
        try {
            $ctx->reflection = $reflection = new \ReflectionClass($class);
        } catch (\ReflectionException $e) {
            throw new ContainerException($e->getMessage(), $e->getCode(), $e);
        }

        // Check scope name
        $scope = ($reflection->getAttributes(ScopeAttribute::class)[0] ?? null)?->newInstance()->name;
        if ($scope !== null && $scope !== $this->scope->getScopeName()) {
            throw new BadScopeException($scope, $class);
        }

        //We have to construct class using external injector when we know exact context
        if ($parameters === [] && $this->binder->hasInjector($class)) {
            return $this->resolveInjector($this->state->bindings[$ctx->class], $ctx->class, $ctx->parameter, $parameters);
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
                $arguments = $this->resolver->resolveArguments($constructor, $parameters);
            } catch (ValidationException $e) {
                throw new ContainerException(
                    $this->tracer->combineTraceMessage(
                        \sprintf(
                            'Can\'t resolve `%s`. %s',
                            $this->tracer->getRootAlias(),
                            $e->getMessage()
                        )
                    ),
                );
            } finally {
                $this->tracer->pop(true);
                $this->tracer->pop(false);
            }
            try {
                // Using constructor with resolved arguments
                $this->tracer->push(false, call: "$class::__construct", arguments: $arguments);
                $this->tracer->push(true);
                $instance = new $class(...$arguments);
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

        return $ctx->reflection->getAttributes(Singleton::class) !== [];
    }

    private function getFinalizer(Ctx $ctx, object $instance): ?callable
    {
        /**
         * @psalm-suppress UnnecessaryVarAnnotation
         * @var Finalize|null $attribute
         */
        $attribute = ($ctx->reflection->getAttributes(Finalize::class)[0] ?? null)?->newInstance();
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
}
