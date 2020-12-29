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
use Spiral\DataGrid\Compiler;
use Spiral\DataGrid\Exception\CompilerException;
use Spiral\DataGrid\Specification;
use Spiral\DataGrid\SpecificationInterface;
use Spiral\DataGrid\WriterInterface;

/**
 * Provides the ability to write into spiral/database SelectQuery and cycle/orm Select.
 */
class PostgresQueryWriter implements WriterInterface
{
    /**
     * @inheritDoc
     */
    public function write($source, SpecificationInterface $specification, Compiler $compiler)
    {
        if (!$this->targetAcceptable($source)) {
            return null;
        }

        if ($specification instanceof Specification\Filter\Postgres\ILike) {
            return $source->where(
                $specification->getExpression(),
                'ILIKE',
                sprintf($specification->getPattern(), $this->fetchValue($specification->getValue()))
            );
        }

        return null;
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

    /**
     * Fetch and assert that filter value is not expecting any user input.
     *
     * @param Specification\ValueInterface|mixed $value
     * @return mixed
     */
    protected function fetchValue($value)
    {
        if ($value instanceof Specification\ValueInterface) {
            throw new CompilerException('Value expects user input, none given');
        }

        return $value;
    }
}
