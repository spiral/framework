<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Filters;

interface FilterInterface
{
    public function isValid(): bool;

    public function getErrors(): array;

    /**
     * Associate the context with the filter.
     *
     * @param mixed $context
     */
    public function setContext($context);

    /**
     * Return currently associated context.
     *
     * @return mixed
     */
    public function getContext();
}
