<?php

declare(strict_types=1);

namespace Spiral\Boot\Injector;

use Spiral\Attributes\AttributeReader;
use Spiral\Core\Container;
use Spiral\Core\Container\InjectorInterface;
use Spiral\Core\Exception\Container\InjectionException;

/**
 * @internal
 */
final class EnumInjector implements InjectorInterface
{
    public function __construct(
        private readonly Container $container,
        private readonly AttributeReader $reader
    ) {
    }

    public function createInjection(\ReflectionClass $class, string $context = null): object
    {
        $attribute = $this->reader->firstClassMetadata($class, ProvideFrom::class);
        if ($attribute === null) {
            throw new InjectionException(
                \sprintf(
                    'Class `%s` should contain `%s` attribute with defined detector method.',
                    $class->getName(),
                    ProvideFrom::class
                )
            );
        }

        $this->validateClass($class, $attribute);

        $object = $this->container->invoke(
            $class->getMethod($attribute->method)->getClosure()
        );

        $this->container->bind($class->getName(), $object);

        return $object;
    }

    /**
     * @throws InjectionException
     */
    private function validateClass(\ReflectionClass $class, ProvideFrom $attribute): void
    {
        if (!$class->isEnum()) {
            throw new InjectionException(
                \sprintf(
                    'Class `%s` should be an enum.',
                    $class->getName()
                )
            );
        }

        if (!$class->hasMethod($attribute->method)) {
            throw new InjectionException(
                \sprintf(
                    'Class `%s` does not contain `%s` method.',
                    $class->getName(),
                    $attribute->method
                )
            );
        }

        if (!$class->getMethod($attribute->method)->isStatic()) {
            throw new InjectionException(
                \sprintf(
                    'Class method `%s::%s` should be static.',
                    $class->getName(),
                    $attribute->method
                )
            );
        }
    }
}
