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
use Spiral\Core\Attribute\Proxy as ProxyAttribute;
use Spiral\Core\Container\Autowire;
use Spiral\Core\Exception\Resolver\ArgumentResolvingException;
use Spiral\Core\Exception\Resolver\InvalidArgumentException;
use Spiral\Core\Exception\Resolver\MissingRequiredArgumentException;
use Spiral\Core\Exception\Resolver\PositionalArgumentException;
use Spiral\Core\Exception\Resolver\ResolvingException;
use Spiral\Core\Exception\Resolver\UnknownParameterException;
use Spiral\Core\Exception\Resolver\UnsupportedTypeException;
use Spiral\Core\FactoryInterface;
use Spiral\Core\Internal\Common\DestructorTrait;
use Spiral\Core\Internal\Common\Registry;
use Spiral\Core\Internal\Resolver\ResolvingState;
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
                /** @psalm-suppress ReferenceReusedFromConfusingScope */
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
     * Returns {@see true} if argument was resolved.
     *
     * @throws ResolvingException
     * @throws NotFoundExceptionInterface|ContainerExceptionInterface
     */
    private function resolveParameter(ReflectionParameter $param, ResolvingState $state, bool $validate): bool
    {
        $isVariadic = $param->isVariadic();
        $hasType = $param->hasType();

        // Try to resolve parameter by name
        $res = $state->resolveParameterByNameOrPosition($param, $isVariadic);
        if ($res !== [] || $isVariadic) {
            // validate
            if ($isVariadic) {
                foreach ($res as $k => &$v) {
                    $this->processArgument($state, $v, validateWith: $validate ? $param : null, key: $k);
                }
            } else {
                $this->processArgument($state, $res[0], validateWith: $validate ? $param : null);
            }

            return true;
        }

        $error = null;
        while ($hasType) {
            /** @var ReflectionIntersectionType|ReflectionUnionType|ReflectionNamedType $refType */
            $refType = $param->getType();

            if ($refType::class === ReflectionNamedType::class) {
                if ($refType->isBuiltin()) {
                    break;
                }

                if (\interface_exists($refType->getName()) && !empty(
                    $attrs = $param->getAttributes(ProxyAttribute::class)
                )) {
                    $proxy = Proxy::create(
                        new \ReflectionClass($refType->getName()),
                        $param,
                        $attrs[0]->newInstance()
                    );
                    $this->processArgument($state, $proxy);
                    return true;
                }

                try {
                    if ($this->resolveObject($state, $refType, $param, $validate)) {
                        return true;
                    }
                } catch (Throwable $e) {
                    $error = $e;
                }
                break;
            }

            if ($refType::class === ReflectionUnionType::class) {
                foreach ($refType->getTypes() as $namedType) {
                    try {
                        if (!$namedType->isBuiltin() && $this->resolveObject($state, $namedType, $param, $validate)) {
                            return true;
                        }
                    } catch (Throwable $e) {
                        $error = $e;
                    }
                }
                break;
            }

            throw new UnsupportedTypeException($param->getDeclaringFunction(), $param->getName());
        }

        if ($param->isDefaultValueAvailable()) {
            $argument = $param->getDefaultValue();
            $this->processArgument($state, $argument);
            return true;
        }

        if ($hasType && $param->allowsNull()) {
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
     * Resolve argument by class name and context. Returns {@see true} if argument resolved.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function resolveObject(
        ResolvingState $state,
        ReflectionNamedType $type,
        ReflectionParameter $parameter,
        bool $validateWith = false,
    ): bool {
        /** @psalm-suppress TooManyArguments */
        $argument = $this->container->get($type->getName(), $parameter);
        $this->processArgument($state, $argument, $validateWith ? $parameter : null);
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
