<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\DBAL\Builders;

use Spiral\Components\DBAL\Database;
use Spiral\Components\DBAL\Parameter;
use Spiral\Components\DBAL\QueryBuilder;
use Spiral\Components\DBAL\QueryCompiler;

class InsertQuery extends QueryBuilder
{
    /**
     * Table name to insert data to, should not include postfix.
     *
     * @var string
     */
    protected $table = '';

    /**
     * Column names should be inserts, every rowset should include this columns is strict order.
     *
     * @var array
     */
    protected $columns = array();

    /**
     * InsertQuery is query builder used to compile insert query into one associated table. It support
     * as single as batch rowsets.
     *
     * @param Database      $database Parent database.
     * @param QueryCompiler $compiler Driver specific QueryGrammar instance (one per builder).
     * @param string        $table    Associated table name.
     */
    public function __construct(Database $database, QueryCompiler $compiler, $table = '')
    {
        parent::__construct($database, $compiler);

        $this->table = $table;
    }

    /**
     * Change target table, table name should be provided without postfix.
     *
     * @param string $into Table name without prefix.
     * @return static
     */
    public function into($into)
    {
        $this->table = $into;

        return $this;
    }

    /**
     * Set insertion column names. Names can be provided as array, set of parameters or comma separated
     * string.
     *
     * Examples:
     * $insert->columns(["name", "email"]);
     * $insert->columns("name", "email");
     * $insert->columns("name, email");
     *
     * @param array|string $columns Array of columns, or comma separated string or multiple parameters.
     * @return static
     */
    public function columns($columns)
    {
        $this->columns = $this->fetchIdentifiers(func_get_args());

        return $this;
    }

    /**
     * Set insertion rowset values or multiple rowsets. Values can be provided in multiple forms
     * (method parameters, array of values, array or rowsets). Columns names will be automatically
     * fetched (if not already specified) from first provided rowset based on rowset keys.
     *
     * Examples:
     * $insert->columns("name", "balance")->values("Wolfy-J", 10);
     * $insert->values([
     *      "name" => "Wolfy-J",
     *      "balance" => 10
     * ]);
     * $insert->values([
     *  [
     *      "name" => "Wolfy-J",
     *      "balance" => 10
     *  ],
     *  [
     *      "name" => "Ben",
     *      "balance" => 20
     *  ]
     * ]);
     *
     * @param mixed $values Array of values, array of rowsets of multiple parameters represents one
     *                      rowset.
     * @return static
     */
    public function values($values)
    {
        if (!is_array($values))
        {
            return $this->values(func_get_args());
        }

        //Checking if provided set is array of multiple
        reset($values);

        $multiple = is_array($values[key($values)]);
        if (!$multiple)
        {
            $this->columns = array_keys($values);
            $this->parameters[] = new Parameter(array_values($values));
        }
        else
        {
            foreach ($values as $rowset)
            {
                $this->parameters[] = new Parameter(array_values($rowset));
            }
        }

        return $this;
    }

    /**
     * Reset all insertion rowsets to make builder reusable (columns still set).
     */
    public function flushValues()
    {
        $this->parameters = array();
    }

    /**
     * Run QueryBuilder statement against parent database. Method will return lastInsertID value.
     *
     * @return mixed
     */
    public function run()
    {
        parent::run();

        return $this->database->getDriver()->lastInsertID();
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

        return $compiler->insert($this->table, $this->columns, $this->parameters);
    }
}