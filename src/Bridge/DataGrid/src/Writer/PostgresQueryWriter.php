<?php

/**
 * Spiral Framework. Data Grid Bridge.
 *
 * @license MIT
 * @author  Anton Tsitou (Wolfy-J)
 * @author  Valentin Vintsukevich (vvval)
 */

declare(strict_types=1);

namespace Spiral\DataGrid\Writer;

use Cycle\ORM\Select;
use Spiral\Database\Driver\Postgres\PostgresDriver;
use Spiral\Database\Query\SelectQuery;
use Spiral\DataGrid\Specification;

/**
 * Provides the ability to write into spiral/database SelectQuery and cycle/orm Select.
 */
class PostgresQueryWriter extends QueryWriter
{
    /**
     * @param Specification\Filter\Expression $filter
     * @return string
     */
    protected function getExpressionOperator(Specification\Filter\Expression $filter): string
    {
        if ($filter instanceof Specification\Filter\Postgres\ILike) {
            return 'ILIKE';
        }

        return parent::getExpressionOperator($filter);
    }

    /**
     * @param mixed $target
     * @return bool
     */
    protected function targetAcceptable($target): bool
    {
        if (
            class_exists(SelectQuery::class)
            && $target instanceof SelectQuery
            && $target->getDriver() instanceof PostgresDriver
        ) {
            return true;
        }

        if (
            class_exists(Select::class)
            && $target instanceof Select
            && $target->buildQuery()->getDriver() instanceof PostgresDriver
        ) {
            return true;
        }

        return false;
    }
}
