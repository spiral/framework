<?php

declare(strict_types=1);

namespace Spiral\Filters\Model\Schema;

use Spiral\Filters\Model\FilterInterface;

interface ReaderInterface
{
    public function read(FilterInterface $filter): array;
}
