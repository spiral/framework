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
use ReflectionUnionType;
use Spiral\Core\Container\Autowire;
use Spiral\Core\Exception\Resolver\ArgumentResolvingException;
use Spiral\Core\Exception\Resolver\InvalidArgumentException;
use Spiral\Core\Exception\Resolver\MissingRequiredArgumentException;
use Spiral\Core\Exception\Resolver\PositionalArgumentException;
use Spiral\Core\Exception\Resolver\ResolvingException;
use Spiral\Core\Exception\Resolver\UnknownParameterException;
use Spiral\Core\Exception\Resolver\UnsupportedTypeException;
use Spiral\Core\FactoryInterface;
use Spiral\Core\ResolverInterface;
use Throwable;

/**
 * @internal
 */
final class Resolver implements ResolverInterface
{
    use DestructorTrait;

    private FactoryInterface $factory;
    private ContainerInterface $container;

    public function __construct(Registry $constructor)
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
            $this->resolveParameter($parameter, $state, $validate)
            or
            throw new ArgumentResolvingException($reflection, $parameter->getName());
        }

        return $state->getResolvedValues();
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

            if ($parameter === null) {
                throw new UnknownParameterException($reflection, $key);
            }
            $name = $parameter->getName();

            if (($positional || $variadic) && $key !== null) {
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

            if (!$this->validateValueToParameter($parameter, $value)) {
                throw new InvalidArgumentException($reflection, $name);
            }
        }
    }

    private function validateValueToParameter(ReflectionParameter $parameter, mixed $value): bool
    {
        if (!$parameter->hasType() || ($parameter->allowsNull() && $value === null)) {
            return true;
        }
        $type = $parameter->getType();

        [$or, $types] = match (true) {
            $type instanceof ReflectionNamedType => [true, [$type]],
            $type instanceof ReflectionUnionType => [true, $type->getTypes()],
            $type instanceof ReflectionIntersectionType => [false, $type->getTypes()],
        };

        foreach ($types as $t) {
            \assert($t instanceof ReflectionNamedType);
            if (!$this->validateValueNamedType($t, $value)) {
                // If it is TypeIntersection
                if ($or) {
                    continue;
                }
                return false;
            }
            // If it is not type intersection then we can skip that value after first successful check
            if ($or) {
                return true;
            }
        }
        return !$or;
    }

    /**
     * Validate the value have the same type that in the $type.
     * This method doesn't resolve cases with nullable type and {@see null} value.
     */
    private function validateValueNamedType(ReflectionNamedType $type, mixed $value): bool
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
    private function resolveParameter(ReflectionParameter $parameter, ResolvingState $state, bool $validate): bool
    {
        $isVariadic = $parameter->isVariadic();
        $hasType = $parameter->hasType();

        // Try to resolve parameter by name
        $res = $state->resolveParameterByNameOrPosition($parameter, $isVariadic);
        if ($res !== [] || $isVariadic) {
            // validate
            if ($isVariadic) {
                foreach ($res as $k => &$v) {
                    $this->processArgument($state, $v, validateWith: $validate ? $parameter : null, key: $k);
                }
            } else {
                $this->processArgument($state, $res[0], validateWith: $validate ? $parameter : null);
            }

            return true;
        }

        $error = null;
        if ($hasType) {
            /** @var ReflectionIntersectionType|ReflectionUnionType|ReflectionNamedType $reflectionType */
            $reflectionType = $parameter->getType();

            if ($reflectionType instanceof ReflectionIntersectionType) {
                throw new UnsupportedTypeException($parameter->getDeclaringFunction(), $parameter->getName());
            }

            $types = $reflectionType instanceof ReflectionNamedType ? [$reflectionType] : $reflectionType->getTypes();
            foreach ($types as $namedType) {
                try {
                    if ($this->resolveNamedType($state, $parameter, $namedType, $validate)) {
                        return true;
                    }
                } catch (Throwable $e) {
                    $error = $e;
                }
            }
        }

        if ($parameter->isDefaultValueAvailable()) {
            $argument = $parameter->getDefaultValue();
            $this->processArgument($state, $argument);
            return true;
        }

        if ($hasType && $parameter->allowsNull()) {
            $argument = null;
            $this->processArgument($state, $argument);
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
        ReflectionNamedType $typeRef,
        bool $validate
    ) {
        return !$typeRef->isBuiltin() && $this->resolveObjectParameter(
            $state,
            $typeRef->getName(),
            $parameter->getName(),
            $validate ? $parameter : null,
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
    private function resolveObjectParameter(
        ResolvingState $state,
        string $class,
        string $context,
        ReflectionParameter $validateWith = null,
    ): bool {
        /** @psalm-suppress TooManyArguments */
        $argument = $this->container->get($class, $context);
        $this->processArgument($state, $argument, $validateWith);
        return true;
    }

    /**
     * Arguments processing. {@see Autowire} object will be resolved.
     *
     * @param mixed $value Resolved value.
     * @param ReflectionParameter|null $validateWith Should be passed when the value should be validated.
     *        Must be set for when value is user's argument.
     * @param int|string|null $key Only {@see string} values will be preserved.
     */
    private function processArgument(
        ResolvingState $state,
        mixed &$value,
        ReflectionParameter $validateWith = null,
        int|string $key = null
    ): void {
        // Resolve Autowire objects
        if ($value instanceof Autowire) {
            $value = $value->resolve($this->factory);
        }

        // Validation
        if ($validateWith !== null && !$this->validateValueToParameter($validateWith, $value)) {
            throw new InvalidArgumentException(
                $validateWith->getDeclaringFunction(),
                $validateWith->getName()
            );
        }

        $state->addResolvedValue($value, \is_string($key) ? $key : null);
    }
}
