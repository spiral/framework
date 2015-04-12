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

class SqlFragment extends Component implements SqlFragmentInterface
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
     * Create or retrieve component instance using IoC container. This method can return already
     * existed instance of class if that instance were defined as singleton and binded in core under
     * same class name. Using binding mechanism target instance can be redefined to use another
     * declaration. Be aware of that.
     *
     * @param string $statement
     * @return SqlFragment
     */
    public static function make($statement = '')
    {
        return parent::make(compact('statement'));
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
        return (object)array(
            'statement' => $this->sqlStatement()
        );
    }
}