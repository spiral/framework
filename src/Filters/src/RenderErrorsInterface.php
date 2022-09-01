<?php

declare(strict_types=1);

namespace Spiral\Filters;

/**
 * @psalm-template F of FilterInterface
 */
interface RenderErrorsInterface
{
    /**
     * @param F $filter
     *
     * @return mixed
     */
    public function render(FilterInterface $filter);
}
