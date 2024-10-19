<?php

declare(strict_types=1);

namespace Spiral\Core\Config;

use Psr\Container\ContainerInterface;

class Proxy extends Binding
{
    /**
     * @template T
     * @param class-string<T> $interface
     * @param null|\Closure(ContainerInterface): T $fallbackFactory
     */
    public function __construct(
        protected readonly string $interface,
        public readonly bool $singleton = false,
        public readonly ?\Closure $fallbackFactory = null,
    ) {
        \interface_exists($interface) or throw new \InvalidArgumentException(
            "Interface `{$interface}` does not exist.",
        );
    }

    public function __toString(): string
    {
        return \sprintf('Proxy to `%s`', $this->interface);
    }

    /**
     * @return class-string
     */
    public function getInterface(): string
    {
        return $this->interface;
    }
}
