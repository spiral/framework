<?php

declare(strict_types=1);

namespace Spiral\Core\Container;

use Spiral\Core\Exception\Container\AutowireException;
use Spiral\Core\Exception\Container\ContainerException;
use Spiral\Core\FactoryInterface;

/**
 * Provides ability to delegate option to container.
 *
 * @template TObject of object
 */
final class Autowire
{
    /** @var null|TObject */
    private ?object $target = null;

    /**
     * Autowire constructor.
     *
     * @param non-empty-string|class-string<TObject> $alias
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
     *
     * @throws AutowireException
     */
    public static function wire(mixed $definition): Autowire
    {
        if ($definition instanceof self) {
            return $definition;
        }

        if (\is_string($definition)) {
            return new self($definition);
        }

        if (\is_array($definition) && isset($definition['class'])) {
            return new self(
                $definition['class'],
                $definition['options'] ?? $definition['params'] ?? []
            );
        }

        if (\is_object($definition)) {
            $autowire = new self($definition::class, []);
            $autowire->target = $definition;
            return $autowire;
        }

        throw new AutowireException('Invalid autowire definition.');
    }

    /**
     * @param array $parameters Context specific parameters (always prior to declared ones).
     * @return TObject
     *
     * @throws AutowireException  No entry was found for this identifier.
     * @throws ContainerException Error while retrieving the entry.
     */
    public function resolve(FactoryInterface $factory, array $parameters = []): object
    {
        return $this->target ?? $factory->make($this->alias, \array_merge($this->parameters, $parameters));
    }
}
