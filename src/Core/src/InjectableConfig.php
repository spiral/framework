<?php

declare(strict_types=1);

namespace Spiral\Core;

use Spiral\Core\Container\InjectableInterface;
use Spiral\Core\Exception\ConfigException;

/**
 * Generic implementation of array based configuration.
 *
 * @implements \IteratorAggregate<array-key, mixed>
 * @implements \ArrayAccess<array-key, mixed>
 */
abstract class InjectableConfig implements InjectableInterface, \IteratorAggregate, \ArrayAccess
{
    /**
     * @var class-string<ConfigsInterface>
     */
    public const INJECTOR = ConfigsInterface::class;

    protected array $config = [];

    /**
     * At this moment on array based configs can be supported.
     * @param array $config Configuration data
     */
    public function __construct(array $config = [])
    {
        $this->config = $config + $this->config;
    }

    /**
     * Restoring state.
     */
    public static function __set_state(array $anArray): static
    {
        return new static($anArray['config']);
    }

    public function toArray(): array
    {
        return $this->config;
    }

    public function offsetExists(mixed $offset): bool
    {
        return \array_key_exists($offset, $this->config);
    }

    public function offsetGet(mixed $offset): mixed
    {
        if (!$this->offsetExists($offset)) {
            throw new ConfigException(\sprintf("Undefined configuration key '%s'", $offset));
        }

        return $this->config[$offset];
    }

    /**
     * @throws ConfigException
     */
    public function offsetSet(mixed $offset, mixed $value): never
    {
        throw new ConfigException(
            'Unable to change configuration data, configs are treated as immutable by default'
        );
    }

    /**
     * @throws ConfigException
     */
    public function offsetUnset(mixed $offset): never
    {
        throw new ConfigException(
            'Unable to change configuration data, configs are treated as immutable by default'
        );
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->config);
    }
}
