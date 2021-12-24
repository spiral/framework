<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Stempler\Node;

interface AttributedInterface
{
    /**
     * @param mixed  $value
     */
    public function setAttribute(string $name, $value): void;

    /**
     * @param mixed  $default If attribute is not set or equal to null.
     * @return mixed
     */
    public function getAttribute(string $name, $default = null);

    public function getAttributes(): array;
}
