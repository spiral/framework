<?php

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Sorter;

use Spiral\Database\Injection\Expression;
use Spiral\Database\Injection\Fragment;

final class ExpressionInjectionSorter extends InjectionSorter
{
    protected const INJECTION = Expression::class;
}
