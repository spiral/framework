<?php

declare(strict_types=1);

namespace Spiral\Core\Config;

use Spiral\Core\Container\InjectorInterface;

/**
 * Means that the value should be injected by an injector.
 *
 * @see InjectorInterface
 */
final class Injectable extends Binding
{
    /**
     * @param string|InjectorInterface $injector Injector object or binding alias.
     */
    public function __construct(
        public readonly string|InjectorInterface $injector,
    ) {
    }

    public function __toString(): string
    {
        return sprintf('Injectable with %s', \is_string($this->injector) ? $this->injector : $this->injector::class);
    }
}
