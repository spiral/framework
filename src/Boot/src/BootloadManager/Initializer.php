<?php

declare(strict_types=1);

namespace Spiral\Boot\BootloadManager;

use Psr\Container\ContainerInterface;
use Spiral\Boot\Bootloader\BootloaderInterface;
use Spiral\Boot\Bootloader\DependedInterface;
use Spiral\Boot\BootloadManagerInterface;
use Spiral\Boot\Exception\ClassNotFoundException;
use Spiral\Core\BinderInterface;
use Spiral\Core\Container;

/**
 * @internal
 * @psalm-import-type TClass from BootloadManagerInterface
 * @psalm-import-type TFullBinding from BootloaderInterface
 */
class Initializer implements InitializerInterface, Container\SingletonInterface
{
    public function __construct(
        protected readonly ContainerInterface $container,
        protected readonly BinderInterface $binder,
        protected readonly ClassesRegistry $bootloaders = new ClassesRegistry()
    ) {
    }

    /**
     * Instantiate bootloader objects and resolve dependencies
     *
     * @param TClass[]|array<TClass, array<string,mixed>> $classes
     */
    public function init(array $classes): \Generator
    {
        foreach ($classes as $bootloader => $options) {
            // default bootload syntax as simple array
            if (\is_string($options) || $options instanceof BootloaderInterface) {
                $bootloader = $options;
                $options = [];
            }

            // Replace class aliases with source classes
            try {
                $ref = (new \ReflectionClass($bootloader));
                $className = $ref->getName();
            } catch (\ReflectionException) {
                throw new ClassNotFoundException(
                    \sprintf('Bootloader class `%s` is not exist.', $bootloader)
                );
            }

            if ($this->bootloaders->isBooted($className) || $ref->isAbstract()) {
                continue;
            }

            $this->bootloaders->register($className);

            if (!$bootloader instanceof BootloaderInterface) {
                $bootloader = $this->container->get($bootloader);
            }

            if (!$this->isBootloader($bootloader)) {
                continue;
            }

            /** @var BootloaderInterface $bootloader */
            yield from $this->initBootloader($bootloader);
            yield $className => \compact('bootloader', 'options');
        }
    }

    public function getRegistry(): ClassesRegistry
    {
        return $this->bootloaders;
    }

    /**
     * Resolve all bootloader dependencies and init bindings
     */
    protected function initBootloader(BootloaderInterface $bootloader): iterable
    {
        if ($bootloader instanceof DependedInterface) {
            yield from $this->init($this->getDependencies($bootloader));
        }

        $this->initBindings(
            $bootloader->defineBindings(),
            $bootloader->defineSingletons()
        );
    }

    /**
     * Bind declared bindings.
     *
     * @param TFullBinding $bindings
     * @param TFullBinding $singletons
     */
    protected function initBindings(array $bindings, array $singletons): void
    {
        foreach ($bindings as $aliases => $resolver) {
            $this->binder->bind($aliases, $resolver);
        }

        foreach ($singletons as $aliases => $resolver) {
            $this->binder->bindSingleton($aliases, $resolver);
        }
    }

    protected function getDependencies(DependedInterface $bootloader): array
    {
        $deps = $bootloader->defineDependencies();

        $reflectionClass = new \ReflectionClass($bootloader);

        $methodsDeps = [];

        foreach (Methods::cases() as $method) {
            if ($reflectionClass->hasMethod($method->value)) {
                $methodsDeps[] = $this->findBootloaderClassesInMethod(
                    $reflectionClass->getMethod($method->value)
                );
            }
        }

        return \array_values(\array_unique(\array_merge($deps, ...$methodsDeps)));
    }

    protected function findBootloaderClassesInMethod(\ReflectionMethod $method): array
    {
        $args = [];
        foreach ($method->getParameters() as $parameter) {
            $type = $parameter->getType();
            if ($type instanceof \ReflectionNamedType && $this->shouldBeBooted($type)) {
                $args[] = $type->getName();
            }
        }

        return $args;
    }

    protected function shouldBeBooted(\ReflectionNamedType $type): bool
    {
        /** @var TClass $class */
        $class = $type->getName();

        /** @psalm-suppress InvalidArgument */
        return $this->isBootloader($class)
            && !$this->bootloaders->isBooted($class);
    }

    /**
     * @psalm-pure
     * @psalm-assert-if-true TClass $class
     */
    protected function isBootloader(string|object $class): bool
    {
        return \is_subclass_of($class, BootloaderInterface::class);
    }
}
