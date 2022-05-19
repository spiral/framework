<?php

declare(strict_types=1);

namespace Spiral\Boot\Injector;

use Spiral\Attributes\AttributeReader;
use Spiral\Core\Container;
use Spiral\Core\Container\InjectorInterface;
use Spiral\Core\Exception\Container\ContainerException;

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
        if (!$attribute) {
            throw new ContainerException(
                \sprintf(
                    'Class `%s` should contain %s attribute with defined detector method.',
                    $class->getName(),
                    ProvideFrom::class
                )
            );
        }

        if (!$class->isEnum()) {
            throw new ContainerException(
                \sprintf(
                    'Class `%s` should be an enum class.',
                    $class->getName()
                )
            );
        }

        $object = $this->container->invoke(
            $class->getMethod($attribute->method)->getClosure()
        );

        $this->container->bind($class->getName(), $object);

        return $object;
    }
}
