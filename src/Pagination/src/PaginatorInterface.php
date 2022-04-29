<?php

declare(strict_types=1);

namespace Spiral\Pagination;

/**
 * Generic paginator interface with ability to set/get page and limit values.
 */
interface PaginatorInterface
{
    /**
     * Paginate the target selection and return new paginator instance.
     */
    public function paginate(PaginableInterface $target): PaginatorInterface;
}
