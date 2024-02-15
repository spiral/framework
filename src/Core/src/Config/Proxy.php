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
    }

    /**
     * @return class-string
     */
    public function getInterface(): string
    {
        return $this->interface;
    }

    public function __toString(): string
    {
        return \sprintf('Proxy to `%s`', $this->interface);
    }
}
