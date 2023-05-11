<?php

declare(strict_types=1);

namespace Spiral\Core\Config;

/**
 * Inflector allow to define the manipulation of an object of a specific type as the final step before
 * it is returned by the container.
 */
final class Inflector extends Binding
{
    /**
     * @param \Closure $inflector The first closure argument is the object to be manipulated.
     *        Closure can return the new or the same object.
     */
    public function __construct(
        public readonly \Closure $inflector,
    ) {
    }
}
