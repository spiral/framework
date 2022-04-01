<?php

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Sorter;

final class DescSorter extends AbstractSorter
{
    public function getValue(): string
    {
        return self::DESC;
    }
}
