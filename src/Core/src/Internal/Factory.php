<?php

declare(strict_types=1);

namespace Spiral\Core\Internal;

use Psr\Container\ContainerInterface;
use Spiral\Core\Container\Autowire;
use Spiral\Core\Container\InjectableInterface;
use Spiral\Core\Container\InjectorInterface;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Core\Exception\Container\AutowireException;
use Spiral\Core\Exception\Container\ContainerException;
use Spiral\Core\Exception\Container\InjectionException;
use Spiral\Core\Exception\Container\NotCallableException;
use Spiral\Core\Exception\Container\NotFoundException;
use Spiral\Core\Exception\Resolver\ArgumentException;
use Spiral\Core\Exception\Resolver\ResolvingException;
use Spiral\Core\Exception\Resolver\WrongTypeException;
use Spiral\Core\FactoryInterface;
use Spiral\Core\InvokerInterface;
use Spiral\Core\ResolverInterface;

/**
 * @internal
 */
final class Factory implements FactoryInterface
{
    use DestructorTrait;

    private State $state;
    private InvokerInterface $invoker;
    private ContainerInterface $container;
    private ResolverInterface $resolver;

    public function __construct(Constructor $constructor)
    {
        $constructor->set('factory', $this);

        $this->state = $constructor->get('state', State::class);
        $this->invoker = $constructor->get('invoker', InvokerInterface::class);
        $this->container = $constructor->get('container', ContainerInterface::class);
        $this->resolver = $constructor->get('resolver', ResolverInterface::class);
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
        if (!isset($this->state->bindings[$alias])) {
            //No direct instructions how to construct class, make is automatically
            return $this->autowire($alias, $parameters, $context);
        }

        $binding = $this->state->bindings[$alias];
        if (\is_object($binding)) {
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
            $this->state->bindings[$alias] = $binding;
        }

        if ($binding[1]) {
            // Indicates singleton
            $this->state->bindings[$alias] = $instance;
        }

        return $instance;
    }

    /**
     * Automatically create class.
     *
     * @throws AutowireException
     * @throws \Throwable
     */
    private function autowire(string $class, array $parameters, string $context = null): object
    {
        if (!\class_exists($class) && !isset($this->state->injectors[$class])) {
            throw new NotFoundException(\sprintf('Undefined class or binding `%s`.', $class));
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
            throw new ContainerException(\sprintf('Invalid binding for `%s`.', $alias), $e->getCode(), $e);
        }
    }

    /**
     * Checks if given class has associated injector.
     */
    private function checkInjector(\ReflectionClass $reflection): bool
    {
        $class = $reflection->getName();
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

    /**
     * Create instance of desired class.
     *
     * @param array $parameters Constructor parameters.
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
        if ($parameters === [] && $this->checkInjector($reflection)) {
            $injector = $this->state->injectors[$reflection->getName()];

            $instance = null;
            try {
                /** @var InjectorInterface|mixed $injectorInstance */
                $injectorInstance = $this->container->get($injector);

                if (!$injectorInstance instanceof InjectorInterface) {
                    throw new InjectionException(
                        \sprintf(
                            "Class '%s' must be an instance of InjectorInterface for '%s'",
                            $injectorInstance::class,
                            $reflection->getName()
                        )
                    );
                }

                $instance = $injectorInstance->createInjection($reflection, $context);
                if (!$reflection->isInstance($instance)) {
                    throw new InjectionException(
                        \sprintf(
                            "Invalid injection response for '%s'",
                            $reflection->getName()
                        )
                    );
                }
            } finally {
                $this->state->injectors[$reflection->getName()] = $injector;
            }

            return $instance;
        }

        if (!$reflection->isInstantiable()) {
            throw new ContainerException(\sprintf('Class `%s` can not be constructed', $class));
        }

        $constructor = $reflection->getConstructor();

        if ($constructor !== null) {
            try {
                // Using constructor with resolved arguments
                $instance = new $class(...$this->resolver->resolveArguments($constructor, $parameters));
            } catch (\TypeError $e) {
                throw new WrongTypeException($constructor, $e);
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
