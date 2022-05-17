<?php

declare(strict_types=1);

namespace Spiral\Core\Internal;

use Spiral\Core\Config;

/**
 * @internal
 */
final class Registry
{
    /**
     * @param array<string, object> $objects
     */
    public function __construct(
        private Config $config,
        private array $objects = [],
    ) {
    }

    public function set(string $name, object $value): void
    {
        $this->objects[$name] = $value;
    }

    /**
     * @template T
     *
     * @param class-string<T> $interface
     *
     * @return T
     */
    public function get(string $name, string $interface): object
    {
        $className = $this->config->$name;
        $result = $this->objects[$name] ?? new $className($this);
        \assert($result instanceof $interface);
        return $result;
    }
}
