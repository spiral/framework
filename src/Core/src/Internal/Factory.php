<?php

declare(strict_types=1);

namespace Spiral\Core\Internal;

use Psr\Container\ContainerInterface;
use Spiral\Core\BinderInterface;
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
use Spiral\Core\FactoryInterface;
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

    public function __construct(Registry $constructor)
    {
        $constructor->set('factory', $this);

        $this->state = $constructor->get('state', State::class);
        $this->binder = $constructor->get('binder', BinderInterface::class);
        $this->invoker = $constructor->get('invoker', InvokerInterface::class);
        $this->container = $constructor->get('container', ContainerInterface::class);
        $this->resolver = $constructor->get('resolver', ResolverInterface::class);
        $this->tracer = $constructor->get('tracer', Tracer::class);
    }

    /**
     * @param string|null $context Related to parameter caused injection if any.
     *
     * @throws \Throwable
     */
    public function make(string $alias, array $parameters = [], string $context = null): mixed
    {
        if (!isset($this->state->bindings[$alias])) {
            $this->tracer->push(false, action: 'autowire', alias: $alias, context: $context);
            try {
                //No direct instructions how to construct class, make is automatically
                return $this->autowire($alias, $parameters, $context);
            } finally {
                $this->tracer->pop(false);
            }
        }

        $binding = $this->state->bindings[$alias];
        try {
            $this->tracer->push(false, action: 'resolve from binding', alias: $alias, context: $context, binding: $binding);
            $this->tracer->push(true);

            if (\is_object($binding)) {
                if ($binding::class === WeakReference::class) {
                    if ($binding->get() === null && \class_exists($alias)) {
                        try {
                            $this->tracer->push(false, alias: $alias, source: WeakReference::class, context: $context);
                            $object = $this->createInstance($alias, $parameters, $context);
                            $binding = $this->state->bindings[$alias] = WeakReference::create($object);
                        } catch (\Throwable) {
                            throw new ContainerException($this->tracer->combineTraceMessage(\sprintf(
                                'Can\'t resolve `%s`: can\'t instantiate `%s` from WeakReference binding.',
                                $this->tracer->getRootAlias(),
                                $alias,
                            )));
                        } finally {
                            $this->tracer->pop();
                        }
                    }
                    return $binding->get();
                }
                //When binding is instance, assuming singleton
                return $binding;
            }

            if (\is_string($binding)) {
                //Binding is pointing to something else
                return $this->make($binding, $parameters, $context);
            }

            unset($this->state->bindings[$alias]);
            try {
                $instance = $binding[0] === $alias
                    ? $this->autowire($alias, $parameters, $context)
                    : $this->evaluateBinding($alias, $binding[0], $parameters, $context);
            } finally {
                /** @psalm-var class-string $alias */
                $this->state->bindings[$alias] = $binding;
            }
        } finally {
            $this->tracer->pop(true);
            $this->tracer->pop(false);
        }

        if ($binding[1]) {
            // Indicates singleton
            /** @psalm-var class-string $alias */
            $this->state->bindings[$alias] = $instance;
        }

        return $instance;
    }

    /**
     * Automatically create class.
     *
     * @param class-string $class
     *
     * @throws AutowireException
     * @throws \Throwable
     */
    private function autowire(string $class, array $parameters, string $context = null): object
    {
        if (!\class_exists($class) && !isset($this->state->injectors[$class])) {
            throw new NotFoundException($this->tracer->combineTraceMessage(\sprintf(
                'Can\'t resolve `%s`: undefined class or binding `%s`.',
                $this->tracer->getRootAlias(),
                $class
            )));
        }

        // automatically create instance
        $instance = $this->createInstance($class, $parameters, $context);

        // apply registration functions to created instance
        return $this->registerInstance($instance, $parameters);
    }

    /**
     * @param mixed $target Value binded by user.
     *
     * @throws ContainerException
     * @throws \Throwable
     */
    private function evaluateBinding(
        string $alias,
        mixed $target,
        array $parameters,
        string $context = null
    ): mixed {
        if (\is_string($target)) {
            // Reference
            return $this->make($target, $parameters, $context);
        }

        if ($target instanceof Autowire) {
            return $target->resolve($this, $parameters);
        }

        try {
            return $this->invoker->invoke($target, $parameters);
        } catch (NotCallableException $e) {
            throw new ContainerException(
                $this->tracer->combineTraceMessage(\sprintf('Invalid binding for `%s`.', $alias)),
                $e->getCode(),
                $e,
            );
        }
    }

    /**
     * Create instance of desired class.
     *
     * @template TObject
     *
     * @param class-string<TObject> $class
     * @param array $parameters Constructor parameters.
     *
     * @return TObject
     *
     * @throws ContainerException
     * @throws \Throwable
     */
    private function createInstance(string $class, array $parameters, string $context = null): object
    {
        try {
            $reflection = new \ReflectionClass($class);
        } catch (\ReflectionException $e) {
            throw new ContainerException($e->getMessage(), $e->getCode(), $e);
        }

        //We have to construct class using external injector when we know exact context
        if ($parameters === [] && $this->binder->hasInjector($class)) {
            $injector = $this->state->injectors[$reflection->getName()];

            try {
                $injectorInstance = $this->container->get($injector);

                if (!$injectorInstance instanceof InjectorInterface) {
                    throw new InjectionException(
                        \sprintf(
                            "Class '%s' must be an instance of InjectorInterface for '%s'.",
                            $injectorInstance::class,
                            $reflection->getName()
                        )
                    );
                }

                /** @var InjectorInterface<TObject> $injectorInstance */
                $instance = $injectorInstance->createInjection($reflection, $context);
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
                $this->state->injectors[$reflection->getName()] = $injector;
            }
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
     * Register instance in container, might perform methods like auto-singletons, log populations
     * and etc. Can be extended.
     *
     * @param object $instance  Created object.
     * @param array $parameters Parameters which been passed with created instance.
     */
    private function registerInstance(object $instance, array $parameters): object
    {
        //Declarative singletons (only when class received via direct get)
        if ($parameters === [] && $instance instanceof SingletonInterface) {
            $alias = $instance::class;
            if (!isset($this->state->bindings[$alias])) {
                $this->state->bindings[$alias] = $instance;
            }
        }

        // Your code can go here (for example LoggerAwareInterface, custom hydration and etc)
        return $instance;
    }
}
