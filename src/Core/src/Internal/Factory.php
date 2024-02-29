<?php

declare(strict_types=1);

namespace Spiral\Core\Internal;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use ReflectionFunctionAbstract as ContextFunction;
use ReflectionParameter;
use Spiral\Core\Attribute;
use Spiral\Core\BinderInterface;
use Spiral\Core\Config;
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
use Spiral\Core\Options;
use Spiral\Core\ResolverInterface;
use Stringable;
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
    private Options $options;

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
        $this->options = $constructor->getOptions();
    }

    /**
     * @param Stringable|string|null $context Related to parameter caused injection if any.
     *
     * @throws \Throwable
     */
    public function make(string $alias, array $parameters = [], Stringable|string|null $context = null): mixed
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
                Config\Alias::class => $this->resolveAlias($binding, $alias, $context, $parameters),
                Config\Proxy::class,
                Config\DeprecationProxy::class => $this->resolveProxy($binding, $alias, $context),
                Config\Autowire::class => $this->resolveAutowire($binding, $alias, $context, $parameters),
                Config\DeferredFactory::class,
                Config\Factory::class => $this->resolveFactory($binding, $alias, $context, $parameters),
                Config\Shared::class => $this->resolveShared($binding, $alias, $context, $parameters),
                Config\Injectable::class => $this->resolveInjector(
                    $binding,
                    new Ctx(alias: $alias, class: $alias, context: $context),
                    $parameters,
                ),
                Config\Scalar::class => $binding->value,
                Config\WeakReference::class => $this
                    ->resolveWeakReference($binding, $alias, $context, $parameters),
                default => $binding,
            };
        } finally {
            $this->state->bindings[$alias] ??= $binding;
            $this->tracer->pop(true);
            $this->tracer->pop(false);
        }
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
                        $reflection->getName()
                    )
                );
            }

            /** @var array<class-string<InjectorInterface>, \ReflectionMethod|false> $cache reflection for extended injectors */
            static $cache = [];
            $extended = $cache[$injectorInstance::class] ??= (
                static fn (\ReflectionType $type): bool =>
                $type::class === \ReflectionUnionType::class || (string)$type === 'mixed'
            )(
                ($refMethod = new \ReflectionMethod($injectorInstance, 'createInjection'))
                    ->getParameters()[1]->getType()
            ) ? $refMethod : false;

            $asIs = $extended && (\is_string($context) || $this->validateArguments($extended, [$reflection, $context]));
            $instance = $injectorInstance->createInjection($reflection, match (true) {
                $asIs => $context,
                $context instanceof ReflectionParameter => $context->getName(),
                default => (string)$context,
            });

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
            $this->state->bindings[$ctx->class] ??= $binding;
        }
    }

    private function resolveAlias(
        Config\Alias $binding,
        string $alias,
        Stringable|string|null $context,
        array $arguments,
    ): mixed {
        $result = $binding->alias === $alias
            ? $this->autowire(
                new Ctx(alias: $alias, class: $binding->alias, context: $context, singleton: $binding->singleton),
                $arguments,
            )
            //Binding is pointing to something else
            : $this->make($binding->alias, $arguments, $context);

        if ($binding->singleton && $arguments === []) {
            $this->state->singletons[$alias] = $result;
        }

        return $result;
    }

    private function resolveProxy(Config\Proxy $binding, string $alias, Stringable|string|null $context): mixed
    {
        $result = Proxy::create(new \ReflectionClass($binding->getInterface()), $context, new Attribute\Proxy());

        if ($binding->singleton) {
            $this->state->singletons[$alias] = $result;
        }

        return $result;
    }

    private function resolveShared(
        Config\Shared $binding,
        string $alias,
        Stringable|string|null $context,
        array $arguments,
    ): object {
        $avoidCache = $arguments !== [];
        return $avoidCache
            ? $this->createInstance(
                new Ctx(alias: $alias, class: $binding->value::class, context: $context),
                $arguments,
            )
            : $binding->value;
    }

    private function resolveAutowire(
        Config\Autowire $binding,
        string $alias,
        Stringable|string|null $context,
        array $arguments,
    ): mixed {
        $instance = $binding->autowire->resolve($this, $arguments);

        $ctx = new Ctx(alias: $alias, class: $alias, context: $context, singleton: $binding->singleton);
        return $this->validateNewInstance($instance, $ctx, $arguments);
    }

    private function resolveFactory(
        Config\Factory|Config\DeferredFactory $binding,
        string $alias,
        Stringable|string|null $context,
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
        Stringable|string|null $context,
        array $arguments,
    ): ?object {
        $avoidCache = $arguments !== [];

        if (($avoidCache || $binding->reference->get() === null) && \class_exists($alias)) {
            try {
                $this->tracer->push(false, alias: $alias, source: WeakReference::class, context: $context);

                $object = $this->createInstance(
                    new Ctx(alias: $alias, class: $alias, context: $context),
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

    private function resolveWithoutBinding(
        string $alias,
        array $parameters = [],
        Stringable|string|null $context = null
    ): mixed {
        $parent = $this->scope->getParentFactory();

        if ($parent !== null) {
            try {
                $this->tracer->push(false, ...[
                    'current scope' => $this->scope->getScopeName(),
                    'jump to parent scope' => $this->scope->getParentScope()->getScopeName(),
                ]);
                /** @psalm-suppress TooManyArguments */
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
                new Ctx(alias: $alias, class: $alias, context: $context),
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
                $args = $this->resolver->resolveArguments($constructor, $arguments, $this->options->validateArguments);
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
