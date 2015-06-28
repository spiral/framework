<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\DBAL\Builders;

use Spiral\Components\DBAL\Builders\Common\AffectQuery;
use Spiral\Components\DBAL\QueryCompiler;

class DeleteQuery extends AffectQuery
{
    /**
     * Change target table, table name should be provided without postfix.
     *
     * @param string $into Table name without prefix.
     * @return static
     */
    public function table($into)
    {
        $this->table = $into;

        return $this;
    }

    /**
     * Get or render SQL statement.
     *
     * @param QueryCompiler $compiler
     * @return string
     */
    public function sqlStatement(QueryCompiler $compiler = null)
    {
        $compiler = !empty($compiler) ? $compiler : $this->compiler;

        return $compiler->delete(
            $this->table,
            $this->joins,
            $this->whereTokens,
            $this->orderBy,
            $this->limit
        );
    }
}