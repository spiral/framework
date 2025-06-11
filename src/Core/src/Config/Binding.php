<?php

declare(strict_types=1);

namespace Spiral\Core\Config;

/**
 * @internal
 */
abstract class Binding implements \Stringable
{
    /**
     * Get the class or interface name of the object that should be returned.
     * May return null if the return type was not detected or is not a class.
     *
     * @return class-string|null
     * @internal
     */
    public function getReturnClass(): ?string
    {
        return null;
    }
}
