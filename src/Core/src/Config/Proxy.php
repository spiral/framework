<?php

declare(strict_types=1);

namespace Spiral\Core\Config;

use Psr\Container\ContainerInterface;

class Proxy extends Binding
{
    /**
     * @template T
     * @param class-string<T> $interface
     * @param null|\Closure(ContainerInterface, \Stringable|string|null): T $fallbackFactory Factory that will be used
     *        to create an instance if the value is resolved from a proxy.
     */
    public function __construct(
        protected readonly string $interface,
        public readonly bool $singleton = false,
        public readonly ?\Closure $fallbackFactory = null,
    ) {
        \interface_exists($interface) or throw new \InvalidArgumentException(
            "Interface `{$interface}` does not exist.",
        );
        $this->singleton and $this->fallbackFactory !== null and throw new \InvalidArgumentException(
            'Singleton proxies must not have a fallback factory.',
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
