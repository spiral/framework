<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\DBAL\Builders\Common;

use Spiral\Components\DBAL\Database;
use Spiral\Components\DBAL\QueryBuilder;
use Spiral\Components\DBAL\QueryCompiler;
use Spiral\Core\Component\LoggerTrait;

abstract class AffectQuery extends QueryBuilder
{
    /**
     * A lot of traits.
     */
    use LoggerTrait, WhereTrait, JoinTrait;

    /**
     * Table name to affect data into, should not include postfix. Setter method is not provided as
     * it can be named differently in different builders.
     *
     * @var string
     */
    protected $table = '';

    /**
     * Array of columns or/and expressions to be used to generate ORDER BY statement. Every orderBy
     * token should include correct identifier (or expression) and sorting direction (ASC, DESC).
     *
     * @var array
     */
    protected $orderBy = array();

    /**
     * Current limit value.
     *
     * @var int
     */
    protected $limit = 0;

    /**
     * AffectQuery is query builder used to compile affection (delete, update) queries for one
     * associated table.
     *
     * @param Database      $database Parent database.
     * @param QueryCompiler $compiler Driver specific QueryGrammar instance (one per builder).
     * @param string        $table    Associated table name.
     * @param array         $where    Initial set of where rules specified as array.
     */
    public function __construct(
        Database $database,
        QueryCompiler $compiler,
        $table = '',
        array $where = array()
    )
    {
        parent::__construct($database, $compiler);

        $this->table = $table;
        !empty($where) && $this->where($where);
    }

    /**
     * Reasonable only if limit of offset values specified, will affect matched records in specified
     * order.
     *
     * @param string $identifier Column or expression of SQLFragment.
     * @param string $direction  Sorting direction, ASC|DESC.
     * @return static
     */
    public function orderBy($identifier, $direction = 'ASC')
    {
        $this->orderBy[] = array($identifier, $direction);

        return $this;
    }

    /**
     * Set number of rows should be affected.
     *
     * @param int $limit
     * @return static
     */
    public function limit($limit = 0)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * Run QueryBuilder statement against parent database. Method will be overloaded by child builder
     * to return correct value. Affect query builder will return count affected rows.
     *
     * @return int
     */
    public function run()
    {
        if (empty($this->whereTokens) && empty($this->limit) && empty($this->joins))
        {
            self::logger()->warning(
                "Affect query performed without any condition or search limitation, "
                . "whole table will be updated."
            );
        }

        return parent::run()->rowCount();
    }
}