<?php

declare(strict_types=1);

namespace Spiral\Pagination;

interface PaginableInterface
{
    /**
     * Set the pagination limit.
     */
    public function limit(int $limit): self;

    /**
     * Set the pagination offset.
     */
    public function offset(int $offset): self;
}
