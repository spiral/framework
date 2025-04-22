<?php

declare(strict_types=1);

namespace Spiral\Prototype;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Spiral\Core\Attribute\Proxy;
use Spiral\Core\Attribute\Singleton;

/**
 * Contains aliases and targets for all declared prototype dependencies.
 */
#[Singleton]
final class PrototypeRegistry
{
    /** @var array<non-empty-string, Dependency> */
    private array $dependencies = [];

    public function __construct(
        #[Proxy]
        private readonly ContainerInterface $container,
    ) {}

    /**
     * Assign class to prototype property.
     *
     * @param non-empty-string $property
     * @param non-empty-string $type
     */
    public function bindProperty(string $property, string $type): void
    {
        $this->dependencies[$property] = Dependency::create($property, $type);
    }

    /**
     * @return array<non-empty-string, Dependency>
     */
    public function getPropertyBindings(): array
    {
        return $this->dependencies;
    }

    /**
     * Resolves the name of prototype dependency into target class name.
     *
     * @param non-empty-string $name
     */
    public function resolveProperty(string $name): Dependency|ContainerExceptionInterface|null
    {
        $dependency = $this->dependencies[$name] ?? null;
        if ($dependency === null) {
            return null;
        }

        try {
            $this->container->get($dependency->type->name());

            return $dependency;
        } catch (ContainerExceptionInterface $e) {
            return $e;
        }
    }
}
