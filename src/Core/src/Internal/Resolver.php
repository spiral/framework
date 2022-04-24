<?php

declare(strict_types=1);

namespace Spiral\Core\Internal;

use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionFunctionAbstract as ContextFunction;
use ReflectionParameter;
use Spiral\Core\Container\Autowire;
use Spiral\Core\Exception\Resolver\ArgumentException;
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
            throw new ArgumentException($reflection, $parameter->getName());
        }

        // Resolve Autowire objects
        foreach ($state->getResolvedValues() as &$v) {
            if ($v instanceof Autowire) {
                $v = $v->resolve($this->factory);
            }
        }
        return $state->getResolvedValues();
    }

    private function resolveParameter(ReflectionParameter $parameter, ResolvingState $state): ?bool
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
                    if ($this->resolveNamedType($state, $namedType)) {
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

        if (!$parameter->isOptional()) {
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

        return null;
    }

    private function resolveNamedType(ResolvingState $state, \ReflectionNamedType $parameter)
    {
        $type = $parameter->getName();
        /** @psalm-var class-string|null $class */
        $class = $parameter->isBuiltin() ? null : $type;
        $isClass = $class !== null || $type === 'object';
        return $isClass && $this->resolveObjectParameter($state, $class);
    }

    private function resolveObjectParameter(ResolvingState $state, ?string $class): bool
    {
        if ($class !== null) {
            /** @var mixed $argument */
            $argument = $this->container->get($class);
            $state->addResolvedValue($argument);
            return true;
        }
        return false;
    }
}
