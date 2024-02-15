<?php

declare(strict_types=1);

namespace Spiral\Core\Config;

class Proxy extends Binding
{
    /**
     * @param class-string $interface
     */
    public function __construct(
        protected readonly string $interface,
        public readonly bool $singleton = false,
    ) {
        if (!\interface_exists($interface)) {
            throw new \InvalidArgumentException(\sprintf('Interface `%s` does not exist.', $interface));
        }
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
