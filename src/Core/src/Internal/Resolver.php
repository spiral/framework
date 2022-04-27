<?php

declare(strict_types=1);

namespace Spiral\Core\Internal;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionFunctionAbstract as ContextFunction;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionType;
use ReflectionUnionType;
use Spiral\Core\Container\Autowire;
use Spiral\Core\Exception\Resolver\ArgumentResolvingException;
use Spiral\Core\Exception\Resolver\MissingRequiredArgumentException;
use Spiral\Core\Exception\Resolver\PositionalArgumentException;
use Spiral\Core\Exception\Resolver\UnknownParameterException;
use Spiral\Core\Exception\Resolver\ValidationException;
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
        array $parameters = [],
        bool $validate = true,
    ): array {
        $state = new ResolvingState($reflection, $parameters);

        foreach ($reflection->getParameters() as $parameter) {
            $this->resolveParameter($parameter, $state)
            or
            throw new ArgumentResolvingException($reflection, $parameter->getName());
        }

        $result = $state->getResolvedValues();

        // Resolve Autowire objects
        foreach ($result as &$v) {
            if ($v instanceof Autowire) {
                $v = $v->resolve($this->factory);
            }
        }

        if ($validate) {
            $this->validateArguments($reflection, $result);
        }

        return $result;
    }

    public function validateArguments(ContextFunction $reflection, array $arguments = []): void
    {
        $positional = true;
        $variadic = false;
        $parameters = $reflection->getParameters();
        if (\count($parameters) === 0) {
            return;
        }

        $parameter = null;
        while (\count($parameters) > 0 || \count($arguments) > 0) {
            // get related argument value
            $key = \key($arguments);

            // For a variadic parameter it's no sense - named or positional argument will be sent
            // But you can't send positional argument after named in any case
            if (\is_int($key) && !$positional) {
                throw new PositionalArgumentException($reflection, $key);
            }

            $positional = $positional && \is_int($key);

            if (!$variadic) {
                $parameter = \array_shift($parameters);
                $variadic = $parameter?->isVariadic() ?? false;
            }

            if ($parameter === null && !$positional) {
                throw new UnknownParameterException($reflection, $key);
            }
            $name = $parameter?->getName();

            if ($positional || $variadic) {
                $value = \array_shift($arguments);
            } elseif ($key === null || !\array_key_exists($name, $arguments)) {
                if ($parameter->isOptional()) {
                    continue;
                }
                throw new MissingRequiredArgumentException($reflection, $name);
            } else {
                $value = &$arguments[$name];
                unset($arguments[$name]);
            }

            if (!$parameter->hasType() || ($parameter->allowsNull() && $value === null)) {
                continue;
            }
            $type = $parameter->getType();
            \assert($type !== null);

            /**
             * @var bool $or
             * @var array<int, ReflectionNamedType> $types
             */
            [$or, $types] = match (true) {
                $type instanceof ReflectionNamedType => [true, [$type]],
                $type instanceof ReflectionUnionType => [true, $type->getTypes()],
                $type instanceof ReflectionIntersectionType => [false, $type->getTypes()],
            };

            foreach ($types as $t) {
                if (!$this->validateValueType($t, $value)) {
                    // If it is TypeIntersection
                    $or or throw new InvalidArgumentException($reflection, $name);
                    continue;
                }
                // If it is not type intersection then we can skip that value after first successful check
                if ($or) {
                    continue 2;
                }
            }
            // Type intersection is OK here
            $or and throw new InvalidArgumentException($reflection, $name);
        }
    }

    /**
     * Validate the value have the same type that in the $type.
     * This method doesn't resolve cases with nullable type and {@see null} value.
     */
    private function validateValueType(ReflectionNamedType $type, mixed $value): bool
    {
        $name = $type->getName();

        if ($type->isBuiltin()) {
            return match ($name) {
                'mixed' => true,
                'string' => \is_string($value),
                'int' => \is_int($value),
                'bool' => \is_bool($value),
                'array' => \is_array($value),
                'callable' => \is_callable($value),
                'iterable' => \is_iterable($value),
                'float' => \is_float($value),
                'object' => \is_object($value),
                'true' => $value === true,
                'false' => $value === false,
                default => false,
            };
        }

        return $value instanceof $name;
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
