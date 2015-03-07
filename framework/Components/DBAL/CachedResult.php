<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\DBAL;

use Spiral\Components\Cache\CacheStore;
use PDO;

class CachedResult extends QueryResult
{
    /**
     * CacheStore class used to store query result.
     *
     * @var CacheStore
     */
    protected $store = null;

    /**
     * Unique query cache id.
     *
     * @var string
     */
    protected $cacheID = '';

    /**
     * Query string (without mounted bindings).
     *
     * @var string
     */
    protected $query = '';

    /**
     * Query data (rowset) automatically fetched from QueryResult and stored as simple array. Bigger queries will "eat"
     * more memory.
     *
     * @var array
     */
    protected $data = array();

    /**
     * As CachedResult can't interact directly with PDOStatement, fetch mode has to be emulated. Currently only ASSOC and
     * NUM modes supported.
     *
     * @var int
     */
    protected $fetchMode = PDO::FETCH_ASSOC;

    /**
     * Column bindings has to be emulated as PDOStatement is not reachable.
     *
     * @var array
     */
    protected $bindings = array();

    /**
     * CacheResult instance used to represent query result fetched from database and stored in CacheStore for desired lifetime.
     * Due no PDOStatement involved in this class some functionality (like fetch modes) can be limited.
     *
     * @param CacheStore $store      CacheStore class used to store query result.
     * @param array      $cacheID    Unique query cache id.
     * @param string     $query      SQL statement with parameter placeholders.
     * @param array      $parameters Parameters to be binded into query.
     * @param array      $data       Resulted rowset (fetched using ASSOC mode).
     */
    public function __construct(CacheStore $store, $cacheID, $query, array $parameters = array(), array $data = array())
    {
        $this->store = $store;
        $this->cacheID = $cacheID;
        $this->query = $query;
        $this->parameters = $parameters;
        $this->data = $data;
        $this->count = count($data);
        $this->cursor = 0;
    }

    /**
     * Flush results stored in CacheStore and CachedResult.
     */
    public function flush()
    {
        $this->data = array();
        $this->count = 0;

        $this->store->delete($this->cacheID);
        $this->cacheID = null;
    }

    /**
     * Query string associated with PDOStatement.
     *
     * @return string
     */
    public function queryString()
    {
        return DatabaseManager::interpolateQuery($this->query, $this->parameters);
    }

    /**
     * Returns the number of columns in the result set.
     *
     * @link http://php.net/manual/en/pdostatement.columncount.php
     * @return int
     */
    public function countColumns()
    {
        return $this->data ? count($this->data[0]) : 0;
    }

    /**
     * Change PDOStatement fetch mode, use PDO::FETCH_ constants to specify required mode. If you want to keep compatibility
     * with CachedQuery do not use other modes than PDO::FETCH_ASSOC and PDO::FETCH_NUM.
     *
     * @link http://php.net/manual/en/pdostatement.setfetchmode.php
     * @param int $mode The fetch mode must be one of the PDO::FETCH_* constants.
     * @return static
     * @throws DBALException
     */
    public function fetchMode($mode)
    {
        if ($mode != PDO::FETCH_ASSOC && $mode != PDO::FETCH_NUM)
        {
            throw new DBALException('Cached query supports only FETCH_ASSOC and FETCH_NUM fetching modes.');
        }

        $this->fetchMode = $mode;

        return $this;
    }

    /**
     * Fetch one result row as array.
     *
     * @param bool $mode The fetch mode must be one of the PDO::FETCH_* constants, PDO::FETCH_ASSOC by default.
     * @return array
     */
    public function fetch($mode = null)
    {
        $mode && $this->fetchMode($mode);
        if ($data = isset($this->data[$this->cursor]) ? $this->data[$this->cursor++] : false)
        {
            foreach ($this->bindings as $columnID => &$variable)
            {
                $variable = $data[$columnID];
            }
        }

        if ($this->fetchMode == PDO::FETCH_NUM)
        {
            return $data ? array_values($data) : false;
        }

        return $data;
    }

    /**
     * Returns a single column from the next row of a result set.
     *
     * @param int $columnID Column number (0 - first column)
     * @return mixed
     */
    public function fetchColumn($columnID = 0)
    {
        return $this->fetch(PDO::FETCH_NUM)[$columnID];
    }

    /**
     * Bind a column to a PHP variable. CachedResult class allows only one binding per column.
     *
     * @link http://www.php.net/manual/en/function.PDOStatement-bindColumn.php
     * @param integer|string $columnID Column number (1 - first column) or name to bind data to.
     * @param mixed          $variable Variable to bind column value to.
     * @return static
     * @throws DBALException
     */
    public function bind($columnID, &$variable)
    {
        if (!$this->data)
        {
            return $this;
        }

        if (is_numeric($columnID))
        {
            //Getting column number
            foreach (array_keys($this->data[0]) as $index => $name)
            {
                if ($index == $columnID - 1)
                {
                    $this->bindings[$name] = &$variable;

                    return $this;
                }
            }

            throw new DBALException("Did not find index '{$columnID}' in the defined columns, it will not be bound.");
        }
        else
        {
            if (!isset($this->data[0][$columnID]))
            {
                throw new DBALException("Did not find column name '{$columnID}' in the defined columns, it will not be bound.");
            }

            $this->bindings[$columnID] = &$variable;
        }

        return $this;
    }

    /**
     * Returns an array containing all of the result set rows, do not use this method on big datasets.
     *
     * @param bool $mode The fetch mode must be one of the PDO::FETCH_* constants, PDO::FETCH_ASSOC by default.
     * @return array
     */
    public function fetchAll($mode = null)
    {
        $mode && $this->fetchMode($mode);

        //So we can properly emulate bindings and etc.
        $result = array();
        foreach ($this as $row)
        {
            $result[] = $row;
        }

        return $result;
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

        return $this->rowData;
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
        $this->cursor = 0;
        $this->rowData = $this->fetch();
    }

    /**
     * Closes the reader cursor, buffer resources will be freed after that.
     *
     * @link http://php.net/manual/en/pdostatement.closecursor.php
     * @return bool
     */
    public function close()
    {
        return true;
    }

    /**
     * Simplified way to dump information.
     *
     * @return object
     */
    public function __debugInfo()
    {
        return (object)array(
            'store'     => get_class($this->store),
            'cacheID'   => $this->cacheID,
            'statement' => $this->queryString(),
            'count'     => $this->count,
            'rows'      => $this->count > static::DUMP_LIMIT ? '[TOO MANY RECORDS TO DISPLAY]' : $this->fetchAll(\PDO::FETCH_ASSOC)
        );
    }
}