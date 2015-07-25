<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ORM;

use Spiral\Core\Component;

class ModelIterator extends Component implements \Iterator, \Countable, \JsonSerializable
{
    /**
     * ORM component used to create model instances.
     *
     * @invisible
     * @var ORM
     */
    protected $orm = null;

    /**
     * ActiveRecord class.
     *
     * @var string
     */
    protected $class = '';

    /**
     * Data to be iterated.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Constructed model instances.
     *
     * @var ActiveRecord[]
     */
    protected $instances = [];

    /**
     * Current iterator position.
     *
     * @var int
     */
    protected $position = 0;

    /**
     * Model Iterator used for lazy data iteration.
     *
     * @param ORM    $orm
     * @param string $class
     * @param array  $data
     */
    public function __construct(ORM $orm, $class, array $data)
    {
        $this->orm = $orm;
        $this->class = $class;
        $this->data = $data;
    }

    /**
     * Count records.
     *
     * @return int
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * Get all active records as array.
     *
     * @return ActiveRecord[]
     */
    public function all()
    {
        $result = [];
        foreach ($this as $item)
        {
            $result[] = $item;
        }

        return $result;
    }

    /**
     * Return the current document.
     *
     * @link http://www.php.net/manual/en/mongocursor.current.php
     * @link http://php.net/manual/en/iterator.current.php
     * @return object
     */
    public function current()
    {
        $data = $this->data[$this->position];
        if (isset($this->instances[$this->position]))
        {
            //Update instance context
            return $this->instances[$this->position]->setContext($data);
        }

        return $this->instances[$this->position] = $this->orm->construct($this->class, $data);
    }

    /**
     * Advances the cursor to the next result.
     *
     * @link http://www.php.net/manual/en/mongocursor.next.php
     */
    public function next()
    {
        $this->position++;
    }

    /**
     * Returns the current result's _id (as string).
     *
     * @link http://www.php.net/manual/en/mongocursor.key.php
     * @return string
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * Checks if the cursor is reading a valid result.
     *
     * @link http://www.php.net/manual/en/mongocursor.valid.php
     * @return bool
     */
    public function valid()
    {
        return isset($this->data[$this->position]);
    }

    /**
     * Returns the cursor to the beginning of the result set.
     *
     * @link http://php.net/manual/en/mongocursor.rewind.php
     */
    public function rewind()
    {
        $this->position = 0;
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
        return $this->all();
    }

    /**
     * Simplified way to dump information.
     *
     * @return ActiveRecord[]
     */
    public function __debugInfo()
    {
        return $this->all();
    }
}