<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Core;

use Spiral\Core\Container\InjectableInterface;
use Spiral\Core\Exception\ConfigException;

/**
 * Generic implementation of array based configuration.
 */
abstract class InjectableConfig implements InjectableInterface, \IteratorAggregate, \ArrayAccess
{
    public const INJECTOR = ConfigsInterface::class;

    /**
     * Configuration data.
     *
     * @var array
     */
    protected $config = [];

    /**
     * At this moment on array based configs can be supported.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Restoring state.
     *
     * @param array $an_array
     *
     * @return static
     */
    public static function __set_state($an_array)
    {
        return new static($an_array['config']);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return $this->config;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->config);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        if (!$this->offsetExists($offset)) {
            throw new ConfigException("Undefined configuration key '{$offset}'");
        }

        return $this->config[$offset];
    }

    /**
     *{@inheritdoc}
     *
     * @throws ConfigException
     */
    public function offsetSet($offset, $value): void
    {
        throw new ConfigException(
            'Unable to change configuration data, configs are treated as immutable by default'
        );
    }

    /**
     *{@inheritdoc}
     *
     * @throws ConfigException
     */
    public function offsetUnset($offset): void
    {
        throw new ConfigException(
            'Unable to change configuration data, configs are treated as immutable by default'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->config);
    }
}
