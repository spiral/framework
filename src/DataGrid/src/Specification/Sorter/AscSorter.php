<?php

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Sorter;

final class AscSorter extends AbstractSorter
{
    public function getValue(): string
    {
        return self::ASC;
    }
}
