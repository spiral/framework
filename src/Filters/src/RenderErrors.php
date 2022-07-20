<?php

declare(strict_types=1);

namespace Spiral\Filters;

/**
 * @psalm-template F of FilterInterface
 */
interface RenderErrors
{
    /**
     * @param F $filter
     *
     * @return mixed
     */
    public function render(FilterInterface $filter);
}
