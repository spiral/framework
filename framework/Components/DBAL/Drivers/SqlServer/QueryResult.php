<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\DBAL\Drivers\SqlServer;

use PDOStatement;
use Spiral\Components\DBAL\QueryResult as BaseQueryResult;

class QueryResult extends BaseQueryResult
{
    /**
     * Helper column used to create limit, offset statements in older versions.
     */
    const ROW_NUMBER_COLUMN = 'spiral_row_number';

    /**
     * Indication that result includes row number column which should be excluded from results.
     *
     * @var bool
     */
    protected $rowNumberColumn = false;

    /**
     * New ResultReader instance.
     *
     * @link http://php.net/manual/en/class.pdostatement.php
     * @param PDOStatement $statement
     * @param array        $parameters
     */
    public function __construct(PDOStatement $statement, array $parameters = array())
    {
        parent::__construct($statement, $parameters);

        if ($this->statement->getColumnMeta($this->countColumns() - 1)['name'] == self::ROW_NUMBER_COLUMN)
        {
            $this->rowNumberColumn = true;
        }
    }

    /**
     * Returns the number of columns in the result set.
     *
     * @link http://php.net/manual/en/pdostatement.columncount.php
     * @return int
     */
    public function countColumns()
    {
        return $this->statement->columnCount() + ($this->rowNumberColumn ? -1 : 0);
    }

    /**
     * Fetch one result row as array. Will remove ROW_NUMBER_COLUMN from result.
     *
     * @param bool $mode The fetch mode must be one of the PDO::FETCH_* constants, PDO::FETCH_ASSOC by default.
     * @return array
     */
    public function fetch($mode = null)
    {
        $result = parent::fetch($mode);
        $result && $this->rowNumberColumn && array_pop($result);

        return $result;
    }

    /**
     * Returns an array containing all of the result set rows, do not use this method on big datasets.
     *
     * @param bool $mode The fetch mode must be one of the PDO::FETCH_* constants, PDO::FETCH_ASSOC by default.
     * @return array
     */
    public function fetchAll($mode = null)
    {
        if (!$this->rowNumberColumn)
        {
            return parent::fetchAll($mode);
        }

        $result = array();
        while ($rowset = $this->fetch($mode))
        {
            $result[] = $rowset;
        }

        return $result;
    }
}