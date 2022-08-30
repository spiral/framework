<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

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
    /** @var object|null */
    private $target;

    /** @var mixed */
    private $alias;

    /** @var array */
    private $parameters;

    /**
     * Autowire constructor.
     */
    public function __construct(string $alias, array $parameters = [])
    {
        $this->alias = $alias;
        $this->parameters = $parameters;
    }

    /**
     * @param $an_array
     * @return static
     */
    public static function __set_state($an_array)
    {
        return new static($an_array['alias'], $an_array['parameters']);
    }

    /**
     * Init the autowire based on string or array definition.
     *
     * @param mixed $definition
     */
    public static function wire($definition): Autowire
    {
        if ($definition instanceof self) {
            return $definition;
        }

        if (is_string($definition)) {
            return new Autowire($definition);
        }

        if (is_array($definition) && isset($definition['class'])) {
            return new Autowire(
                $definition['class'],
                $definition['options'] ?? $definition['params'] ?? []
            );
        }

        if (is_object($definition)) {
            $autowire = new self(get_class($definition), []);
            $autowire->target = $definition;
            return $autowire;
        }

        throw new AutowireException('Invalid autowire definition');
    }

    /**
     * @param array            $parameters Context specific parameters (always prior to declared ones).
     * @return mixed
     *
     * @throws AutowireException  No entry was found for this identifier.
     * @throws ContainerException Error while retrieving the entry.
     */
    public function resolve(FactoryInterface $factory, array $parameters = [])
    {
        if ($this->target !== null) {
            // pre-wired
            return $this->target;
        }

        return $factory->make($this->alias, array_merge($this->parameters, $parameters));
    }
}
