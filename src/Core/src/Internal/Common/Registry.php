<?php

declare(strict_types=1);

namespace Spiral\Core\Internal\Common;

use Spiral\Core\Config;
use Spiral\Core\Options;

/**
 * @internal
 */
final class Registry
{
    /**
     * @param array<string, object> $objects
     */
    public function __construct(
        private readonly Config $config,
        private array $objects = [],
        private readonly Options $options = new Options(),
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

    public function getOptions(): Options
    {
        return $this->options;
    }
}
