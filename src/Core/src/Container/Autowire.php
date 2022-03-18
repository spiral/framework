<?php

declare(strict_types=1);

namespace Spiral\Core\Container;

use Spiral\Core\Exception\Container\AutowireException;
use Spiral\Core\Exception\Container\ContainerException;
use Spiral\Core\FactoryInterface;

/**
 * Provides ability to delegate option to container.
 */
final class Autowire
{
    private ?object $target = null;

    /**
     * Autowire constructor.
     */
    public function __construct(
        private readonly string $alias,
        private readonly array $parameters = []
    ) {
    }

    public static function __set_state(array $anArray): static
    {
        return new self($anArray['alias'], $anArray['parameters']);
    }

    /**
     * Init the autowire based on string or array definition.
     */
    public static function wire(string|array|object $definition): Autowire
    {
        if ($definition instanceof self) {
            return $definition;
        }

        if (\is_string($definition)) {
            return new Autowire($definition);
        }

        if (\is_array($definition) && isset($definition['class'])) {
            return new Autowire(
                $definition['class'],
                $definition['options'] ?? $definition['params'] ?? []
            );
        }

        if (\is_object($definition)) {
            $autowire = new self($definition::class, []);
            $autowire->target = $definition;
            return $autowire;
        }

        throw new AutowireException('Invalid autowire definition');
    }

    /**
     * @param array $parameters Context specific parameters (always prior to declared ones).
     *
     * @throws AutowireException  No entry was found for this identifier.
     * @throws ContainerException Error while retrieving the entry.
     */
    public function resolve(FactoryInterface $factory, array $parameters = []): object
    {
        if ($this->target !== null) {
            // pre-wired
            return $this->target;
        }

        return $factory->make($this->alias, \array_merge($this->parameters, $parameters));
    }
}
