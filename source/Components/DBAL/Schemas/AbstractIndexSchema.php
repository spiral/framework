<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\DBAL\Schemas;

use Spiral\Core\Component;

abstract class AbstractIndexSchema extends Component
{
    /**
     * Index types.
     */
    const NORMAL = 'INDEX';
    const UNIQUE = 'UNIQUE';

    /**
     * Parent table schema.
     *
     * @invisible
     * @var AbstractTableSchema
     */
    protected $table = null;

    /**
     * Index name.
     *
     * @var string
     */
    protected $name = '';

    /**
     * Index type, by default NORMAL and UNIQUE indexes supported, additional types can be implemented
     * on database driver level.
     *
     * @var int
     */
    protected $type = self::NORMAL;

    /**
     * Column names used to form index.
     *
     * @var array
     */
    protected $columns = [];

    /**
     * Instance on IndexSchema represent one table index - name, type and involved columns. Attention,
     * based on index mapping and resolving (based on set of column name), there is no simple way to
     * create multiple indexes with same set of columns, as they will be resolved as one index.
     *
     * @param AbstractTableSchema $table
     * @param  string             $name
     * @param mixed               $schema Index information fetched from database by TableSchema.
     *                                    Format depends on database type.
     */
    public function __construct(AbstractTableSchema $table, $name, $schema = null)
    {
        $this->name = $name;
        $this->table = $table;

        $schema && $this->resolveSchema($schema);
    }

    /**
     * Parse index information provided by parent TableSchema and populate index values.
     *
     * @param mixed $schema Index information fetched from database by TableSchema. Format depends
     *                      on driver type.
     * @return mixed
     */
    abstract protected function resolveSchema($schema);

    /**
     * Index name. Name can be changed by calling name($name) method, by default all indexes will
     * get automatically generated identifier including table name and index columns.
     *
     * @param bool $quoted If true index name will be quoted accordingly to driver rules.
     * @return string
     */
    public function getName($quoted = false)
    {
        $name = $this->name;
        if (empty($this->name))
        {
            $name = $this->table->getName() . '_index_' . join('_', $this->columns) . '_' . uniqid();
        }

        if (strlen($name) > 64)
        {
            //Many dbs has limitations on identifier length
            $name = md5($name);
        }

        return $quoted ? $this->table->getDriver()->identifier($name) : $name;
    }

    /**
     * Give new name to index. Do not use this method to rename indexes, however it can be used to
     * give initial custom name for newly created indexes. If you really need to rename existed index,
     * use TableSchema->renameIndex() method.
     *
     * @param string $name New index name.
     * @return static
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Check if index is unique.
     *
     * @return bool
     */
    public function isUnique()
    {
        return $this->type == self::UNIQUE;
    }

    /**
     * Change index type and behaviour to unique/non-unique state.
     *
     * @param bool $unique
     * @return static
     */
    public function unique($unique = true)
    {
        $this->type = $unique ? self::UNIQUE : self::NORMAL;

        return $this;
    }

    /**
     * Column names used to form index.
     *
     * @@return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Change set of index columns. Method support both array and string parameters.
     *
     * Example:
     * $index->columns('key');
     * $index->columns('key', 'key2');
     * $index->columns(array('key', 'key2'));
     *
     * @param string|array $columns Columns array or comma separated list of parameters.
     * @return static
     */
    public function columns($columns)
    {
        if (!is_array($columns))
        {
            $columns = func_get_args();
        }

        $this->columns = $columns;

        return $this;
    }

    /**
     * Drop index from table schema. This method will also force index erasing from database table
     * schema on TableSchema->save() method call.
     */
    public function drop()
    {
        $this->table->dropIndex($this->getName());
    }

    /**
     * Compare two index schemas to check if data were altered.
     *
     * @param AbstractIndexSchema $dbIndex
     * @return bool
     */
    public function compare(AbstractIndexSchema $dbIndex)
    {
        return $this == $dbIndex;
    }

    /**
     * Compile index definition statement, such statement can be used in both create and alter index
     * commands.
     *
     * @param bool $includeTable Include table ON statement (not required for inline index creation).
     * @return string
     */
    public function sqlStatement($includeTable = true)
    {
        $statement = [];
        $statement[] = $this->type . ($this->type == self::UNIQUE ? ' INDEX' : '');
        $statement[] = $this->getName(true);

        if ($includeTable)
        {
            $statement[] = 'ON ' . $this->table->getName(true);
        }

        $statement[] = '(' . join(', ', array_map(
                [$this->table->getDriver(), 'identifier'],
                $this->columns
            )) . ')';

        return join(' ', $statement);
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
}