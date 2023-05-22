<?php

declare(strict_types=1);

namespace Spiral\Boot\Injector;

use Spiral\Attributes\AttributeReader;
use Spiral\Core\BinderInterface;
use Spiral\Core\Container\InjectorInterface;
use Spiral\Core\Exception\Container\InjectionException;
use Spiral\Core\InvokerInterface;
use UnitEnum;

/**
 * @implements InjectorInterface<UnitEnum>
 *
 * @internal
 */
final class EnumInjector implements InjectorInterface
{
    public function __construct(
        private readonly InvokerInterface $invoker,
        private readonly BinderInterface $binder,
        private readonly AttributeReader $reader
    ) {
    }

    public function createInjection(\ReflectionClass $class, string $context = null): UnitEnum
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
        /** @var ?\Closure $closure */
        $closure = $class->getMethod($attribute->method)->getClosure();
        \assert($closure !== null);

        $object = $this->invoker->invoke($closure);
        \assert($object instanceof UnitEnum, \sprintf(
            'The method `%s::%s` must provide the same enum instance.',
            $class->getName(),
            $attribute->method,
        ));

        $this->binder->bind($class->getName(), $object);

        return $object;
    }

    /**
     * @psalm-assert \ReflectionClass<UnitEnum> $class
     *
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
