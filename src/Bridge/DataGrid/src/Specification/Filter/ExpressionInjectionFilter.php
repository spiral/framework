<?php

/**
 * Spiral Framework. Data Grid Bridge.
 *
 * @license MIT
 * @author  Valentin Vintsukevich (vvval)
 */

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Filter;

use Cycle\Database\Injection;

class ExpressionInjectionFilter extends InjectionFilter
{
    protected const INJECTION = Injection\Expression::class;
}
