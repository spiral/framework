<?php

declare(strict_types=1);

namespace Spiral\Prototype;

use Psr\Container\ContainerExceptionInterface;
use Spiral\Core\Container;

/**
 * Contains aliases and targets for all declared prototype dependencies.
 */
final class PrototypeRegistry
{
    /** @var Dependency[] */
    private array $dependencies = [];

    /**
     * PrototypeRegistry constructor.
     */
    public function __construct(
        private readonly Container $container
    ) {
    }

    /**
     * Assign class to prototype property.
     */
    public function bindProperty(string $property, string $type): void
    {
        $this->dependencies[$property] = Dependency::create($property, $type);
    }

    /**
     * @return Dependency[]
     */
    public function getPropertyBindings(): array
    {
        return $this->dependencies;
    }

    /**
     * Resolves the name of prototype dependency into target class name.
     *
     * @return Dependency|null|ContainerExceptionInterface
     */
    public function resolveProperty(string $name): mixed
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
