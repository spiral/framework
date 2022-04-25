<?php

declare(strict_types=1);

namespace Spiral\Core\Internal;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionFunctionAbstract as ContextFunction;
use ReflectionParameter;
use Spiral\Core\Container\Autowire;
use Spiral\Core\Exception\Resolver\ArgumentResolvingException;
use Spiral\Core\Exception\Resolver\InvalidArgumentException;
use Spiral\Core\Exception\Resolver\ResolvingException;
use Spiral\Core\Exception\Resolver\UnsupportedTypeException;
use Spiral\Core\FactoryInterface;
use Spiral\Core\ResolverInterface;

/**
 * @internal
 */
final class Resolver implements ResolverInterface
{
    use DestructorTrait;

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
        $state = new ResolvingState($reflection, $parameters);

        foreach ($reflection->getParameters() as $parameter) {
            $this->resolveParameter($parameter, $state)
            or
            throw new ArgumentResolvingException($reflection, $parameter->getName());
        }

        // Resolve Autowire objects
        foreach ($state->getResolvedValues() as &$v) {
            if ($v instanceof Autowire) {
                $v = $v->resolve($this->factory);
            }
        }
        return $state->getResolvedValues();
    }

    public function validateArguments(ContextFunction $reflection, array $arguments = []): void
    {
        foreach ($reflection->getParameters() as $parameter) {
            $name = $parameter->name;
            if (!\array_key_exists($name, $arguments)) {
                if ($parameter->isOptional()) {
                    continue;
                }
                throw new InvalidArgumentException($reflection, $name);
            }

            $value = &$arguments[$name];
            unset($arguments[$name]);

            if (!$parameter->hasType() || ($parameter->allowsNull() && $parameter === $value)) {
                continue;
            }

            // todo: union types
            // todo: type intersection
            // todo: single types

            throw new InvalidArgumentException($reflection, $name);
        }
    }

    /**
     * @return bool {@see true} if argument was resolved.
     *
     * @throws ResolvingException
     * @throws NotFoundExceptionInterface|ContainerExceptionInterface
     */
    private function resolveParameter(ReflectionParameter $parameter, ResolvingState $state): bool
    {
        $isVariadic = $parameter->isVariadic();
        $hasType = $parameter->hasType();

        // Try to resolve parameter by name
        if ($state->resolveParameterByNameOrPosition($parameter, $isVariadic) || $isVariadic) {
            return true;
        }

        $error = null;
        if ($hasType) {
            $reflectionType = $parameter->getType();

            if ($reflectionType instanceof \ReflectionIntersectionType) {
                throw new UnsupportedTypeException($parameter->getDeclaringFunction(), $parameter->getName());
            }

            $types = $reflectionType instanceof \ReflectionNamedType ? [$reflectionType] : $reflectionType->getTypes();
            foreach ($types as $namedType) {
                try {
                    if ($this->resolveNamedType($state, $parameter, $namedType)) {
                        return true;
                    }
                } catch (NotFoundExceptionInterface $e) {
                    $error = $e;
                }
            }
        }

        if ($parameter->isDefaultValueAvailable()) {
            $argument = $parameter->getDefaultValue();
            $state->addResolvedValue($argument);
            return true;
        }

        if ($hasType && $parameter->allowsNull()) {
            $argument = null;
            $state->addResolvedValue($argument);
            return true;
        }

        if ($error === null) {
            return false;
        }

        // Throw NotFoundExceptionInterface
        throw $error;
    }

    /**
     * Resolve single named type.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     *
     * @return bool {@see true} if argument was resolved.
     */
    private function resolveNamedType(
        ResolvingState $state,
        ReflectionParameter $parameter,
        \ReflectionNamedType $typeRef
    ) {
        return !$typeRef->isBuiltin() && $this->resolveObjectParameter(
            $state,
            $typeRef->getName(),
            $parameter->getName()
        );
    }

    /**
     * Resolve argument by class name and context.
     *
     * @psalm-param class-string $class
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     *
     * @return bool {@see true} if argument resolved.
     */
    private function resolveObjectParameter(ResolvingState $state, string $class, string $context): bool
    {
        /** @var mixed $argument */
        $argument = $this->container->get($class, $context);
        $state->addResolvedValue($argument);
        return true;
    }
}
