<?php

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Sorter;

use Cycle\Database\Injection\Expression;

/**
 * @deprecated since v2.9. Will be moved to spiral/cycle-bridge and removed in v3.0
 */
final class ExpressionInjectionSorter extends InjectionSorter
{
    protected const INJECTION = Expression::class;
}
