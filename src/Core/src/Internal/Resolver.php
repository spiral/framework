<?php

declare(strict_types=1);

namespace Spiral\Core\Internal;

use Psr\Container\ContainerInterface;
use ReflectionFunctionAbstract as ContextFunction;
use Spiral\Core\Container\Autowire;
use Spiral\Core\Exception\Container\ArgumentException;
use Spiral\Core\Exception\Container\AutowireException;
use Spiral\Core\Exception\Container\ContainerException;
use Spiral\Core\FactoryInterface;
use Spiral\Core\ResolverInterface;

/**
 * @internal
 */
final class Resolver implements ResolverInterface
{
    private FactoryInterface $factory;
    private ContainerInterface $container;

    public function __construct(Constructor $constructor)
    {
        $constructor->set('resolver', $this);

        $this->factory = $constructor->get('factory', FactoryInterface::class);
        $this->container = $constructor->get('container', ContainerInterface::class);
    }

    public function resolveArguments(
        ContextFunction $reflection,
        array $parameters = []
    ): array {
        $arguments = [];

        foreach ($reflection->getParameters() as $parameter) {
            $type = $parameter->getType();
            $name = $parameter->getName();
            $class = null;

            /**
             * Container do not currently support union types. In the future, we
             * can provide the possibility of autowiring based on priorities (TBD).
             */
            if ($type instanceof \ReflectionUnionType) {
                $error = 'Parameter $%s in %s contains a union type hint that cannot be inferred unambiguously';
                $error = \sprintf($error, $reflection->getName(), $this->getLocationString($reflection));

                throw new ContainerException($error);
            }

            /**
             * Container do not currently support intersection types.
             */
            if ($type instanceof \ReflectionIntersectionType) {
                $error = 'Parameter $%s in %s contains a intersection type hint that cannot be inferred unambiguously';
                $error = \sprintf($error, $reflection->getName(), $this->getLocationString($reflection));

                throw new ContainerException($error);
            }

            if ($type instanceof \ReflectionNamedType && !$type->isBuiltin()) {
                try {
                    $class = new \ReflectionClass($type->getName());
                } catch (\ReflectionException $e) {
                    $location = $this->getLocationString($reflection);

                    $error = 'Unable to resolve `\$%s` parameter in %s: %s';
                    $error = \sprintf($error, $parameter->getName(), $location, $e->getMessage());

                    throw new ContainerException($error, $e->getCode(), $e);
                }
            }

            if (isset($parameters[$name]) && \is_object($parameters[$name])) {
                if ($parameters[$name] instanceof Autowire) {
                    // Supplied by user as late dependency
                    $arguments[] = $parameters[$name]->resolve($this->factory);
                } else {
                    // Supplied by user as object
                    $arguments[] = $parameters[$name];
                }
                continue;
            }

            // no declared type or scalar type or array
            if (!isset($class)) {
                // Provided from outside
                if (\array_key_exists($name, $parameters)) {
                    // Make sure it's properly typed
                    $this->assertType($parameter, $reflection, $parameters[$name]);
                    $arguments[] = $parameters[$name];
                    continue;
                }

                if ($parameter->isDefaultValueAvailable()) {
                    //Default value
                    $arguments[] = $parameter->getDefaultValue();
                    continue;
                }

                //Unable to resolve scalar argument value
                throw new ArgumentException($parameter, $reflection);
            }

            try {
                //Requesting for contextual dependency
                $arguments[] = $this->container->get($class->getName(), $name);
                continue;
            } catch (AutowireException $e) {
                if ($parameter->isOptional()) {
                    //This is optional dependency, skip
                    $arguments[] = null;
                    continue;
                }

                throw $e;
            }
        }

        return $arguments;
    }

    private function getLocationString(ContextFunction $reflection): string
    {
        $location = $reflection->getName();

        if ($reflection instanceof \ReflectionMethod) {
            return "{$reflection->getDeclaringClass()->getName()}::{$location}()";
        }

        return $location;
    }

    /**
     * Assert that given value are matched parameter type.
     *
     * @throws ArgumentException
     * @throws \ReflectionException
     */
    private function assertType(\ReflectionParameter $parameter, ContextFunction $context, mixed $value): void
    {
        if ($value === null) {
            if (!$parameter->allowsNull()) {
                throw new ArgumentException($parameter, $context);
            }

            return;
        }

        $type = $parameter->getType();
        if ($type === null) {
            return;
        }

        $typeName = $type->getName();
        if ($typeName === 'array' && !\is_array($value)) {
            throw new ArgumentException($parameter, $context);
        }

        if (($typeName === 'int' || $typeName === 'float') && !\is_numeric($value)) {
            throw new ArgumentException($parameter, $context);
        }

        if ($typeName === 'bool' && !\is_bool($value) && !\is_numeric($value)) {
            throw new ArgumentException($parameter, $context);
        }
    }
}
