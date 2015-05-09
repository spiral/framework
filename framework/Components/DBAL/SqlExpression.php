<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\DBAL;

class SqlExpression extends SqlFragment
{
    /**
     * Get or render SQL statement. SQLExpression is different than SQLFragment as it content will
     * be quoted and prefixed using QueryCompiler.
     *
     * Example use:
     * new SQLExpression("table.column_a + table.column_b")
     *
     * Upon query compilation expression will be converted to valid sql:
     * ... "prefix_table.column_a" + "prefix_table.column_b"
     *
     * @param QueryCompiler $compiler
     * @return string
     */
    public function sqlStatement(QueryCompiler $compiler = null)
    {
        if (empty($compiler))
        {
            return $this->statement;
        }

        return $compiler->quote($this->statement);
    }
}