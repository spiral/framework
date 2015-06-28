<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\DBAL;

use Spiral\Core\Component;

class SqlFragment implements SqlFragmentInterface
{
    /**
     * Raw SQL statement.
     *
     * @var string
     */
    protected $statement = null;

    /**
     * New SQLFragment instance. SQLFragments used to bypass Database->quote() method or in query
     * builders to inject SQL at place of conditional, update or insert parameter (bypass PDOStatement
     * prepare).
     *
     * @param string $statement
     */
    public function __construct($statement)
    {
        $this->statement = $statement;
    }

    /**
     * Get or render SQL statement.
     *
     * @param QueryCompiler $compiler
     * @return string
     */
    public function sqlStatement(QueryCompiler $compiler = null)
    {
        return $this->statement;
    }

    /**
     * __toString
     *
     * @return string
     */
    public function __toString()
    {
        return $this->sqlStatement();
    }

    /**
     * Simplified way to dump information.
     *
     * @return object
     */
    public function __debugInfo()
    {
        return (object)[
            'statement' => $this->sqlStatement()
        ];
    }
}