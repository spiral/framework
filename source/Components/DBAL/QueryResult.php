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
use PDOStatement;

class QueryResult extends Component implements \Countable, \Iterator, \JsonSerializable
{
    /**
     * Limits after which no records will be dumped in __debugInfo.
     */
    const DUMP_LIMIT = 500;

    /**
     * PDOStatement generated for selection query.
     *
     * @invisible
     * @var PDOStatement
     */
    protected $statement = null;

    /**
     * PDOStatement prepare parameters.
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * Cursor position, used to determinate current data index.
     *
     * @var int
     */
    protected $cursor = null;

    /**
     * The number of rows selected by SQL statement.
     *
     * @var int
     */
    protected $count = 0;

    /**
     * Last selected row array. Having this value is required to correctly emulate Iterator methods.
     *
     * @var mixed
     */
    protected $rowData = null;

    /**
     * New ResultReader instance.
     *
     * @link http://php.net/manual/en/class.pdostatement.php
     * @param PDOStatement $statement
     * @param array        $parameters
     */
    public function __construct(PDOStatement $statement, array $parameters = [])
    {
        $this->statement = $statement;
        $this->parameters = $parameters;

        $this->count = $this->statement->rowCount();

        //Forcing default fetch mode
        $this->statement->setFetchMode(\PDO::FETCH_ASSOC);
    }

    /**
     * Query string associated with PDOStatement.
     *
     * @return string
     */
    public function queryString()
    {
        return DatabaseManager::interpolateQuery($this->statement->queryString, $this->parameters);
    }

    /**
     * Returns the number of rows selected by SQL statement. Attention, this method will return 0
     * for SQLite databases.
     *
     * @link http://php.net/manual/en/pdostatement.rowcount.php
     * @link http://stackoverflow.com/questions/15003232/pdo-returns-wrong-rowcount-after-select-statement
     * @return int
     */
    public function count()
    {
        return $this->count;
    }

    /**
     * Returns the number of columns in the result set.
     *
     * @link http://php.net/manual/en/pdostatement.columncount.php
     * @return int
     */
    public function countColumns()
    {
        return $this->statement->columnCount();
    }

    /**
     * Change PDOStatement fetch mode, use PDO::FETCH_ constants to specify required mode. If you wan
     * t to keep compatibility with CachedQuery do not use other modes than PDO::FETCH_ASSOC and
     * PDO::FETCH_NUM.
     *
     * @link http://php.net/manual/en/pdostatement.setfetchmode.php
     * @param int $mode The fetch mode must be one of the PDO::FETCH_* constants.
     * @return $this
     */
    public function fetchMode($mode)
    {
        $this->statement->setFetchMode($mode);

        return $this;
    }

    /**
     * Fetch one result row as array.
     *
     * @param bool $mode The fetch mode must be one of the PDO::FETCH_* constants, PDO::FETCH_ASSOC
     *                   by default.
     * @return array
     */
    public function fetch($mode = null)
    {
        if (!empty($mode))
        {
            $this->fetchMode($mode);
        }

        return $this->statement->fetch();
    }

    /**
     * Returns a single column from the next row of a result set.
     *
     * @param int $columnID Column number (0 - first column)
     * @return mixed
     */
    public function fetchColumn($columnID = 0)
    {
        return $this->statement->fetchColumn($columnID);
    }

    /**
     * Bind a column to a PHP variable.
     *
     * @link http://www.php.net/manual/en/function.PDOStatement-bindColumn.php
     * @param integer|string $columnID Column number (1 - first column) or name to bind data to.
     * @param mixed          $variable Variable to bind column value to.
     * @return $this
     */
    public function bind($columnID, &$variable)
    {
        $this->statement->bindColumn($columnID, $variable);

        return $this;
    }

    /**
     * Returns an array containing all of the result set rows, do not use this method on big datasets.
     *
     * @param bool $mode The fetch mode must be one of the PDO::FETCH_* constants, PDO::FETCH_ASSOC
     *                   by default.
     * @return array
     */
    public function fetchAll($mode = null)
    {
        if (!empty($mode))
        {
            $this->fetchMode($mode);
        }

        return $this->statement->fetchAll();
    }

    /**
     * (PHP 5 >= 5.0.0)
     * Return the current element
     *
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed
     */
    public function current()
    {
        return $this->rowData;
    }

    /**
     * (PHP 5 >= 5.0.0)
     * Move forward to next element, returns currently selected element.
     *
     * @link http://php.net/manual/en/iterator.next.php
     * @return bool|mixed
     */
    public function next()
    {
        $this->rowData = $this->fetch();
        $this->cursor++;

        return $this->rowData;
    }

    /**
     * (PHP 5 >= 5.0.0)
     * Return the key of the current element.
     *
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed
     */
    public function key()
    {
        return $this->cursor;
    }

    /**
     * (PHP 5 >= 5.0.0)
     * Checks if current position is valid.
     *
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean
     */
    public function valid()
    {
        //We can't use cursor or any other method to walk though data as SQLite will return 0 for count.
        return $this->rowData !== false;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)
     * Rewind the Iterator to the first element.
     *
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void
     */
    public function rewind()
    {
        $this->rowData = $this->fetch();
        $this->cursor = 0;
    }

    /**
     * Closes the reader cursor, buffer resources will be freed after that.
     *
     * @link http://php.net/manual/en/pdostatement.closecursor.php
     * @return bool
     */
    public function close()
    {
        return $this->statement && $this->statement->closeCursor();
    }

    /**
     * Destruct ResultReader and free all used memory.
     */
    public function __destruct()
    {
        $this->close();
        $this->statement = null;
    }

    /**
     * Simplified way to dump information.
     *
     * @return object
     */
    public function __debugInfo()
    {
        return (object)[
            'statement' => $this->queryString(),
            'count'     => $this->count,
            'rows'      => $this->count > static::DUMP_LIMIT
                ? '[TOO MANY RECORDS TO DISPLAY]'
                : $this->fetchAll(\PDO::FETCH_ASSOC)
        ];
    }

    /**
     * (PHP 5 > 5.4.0)
     * Specify data which should be serialized to JSON.
     *
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed
     */
    public function jsonSerialize()
    {
        return $this->fetchAll();
    }
}