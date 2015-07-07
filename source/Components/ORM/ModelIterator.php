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

/**
 * TODO: NOT FINISHED YET.
 */
class ModelIterator extends Component implements \Iterator
{
    /**
     *
     * @invisible
     * @var ORM
     */
    protected $orm = null;

    protected $class = '';
    protected $data = [];


    protected $position = 0;

    public function __construct(ORM $orm, $class, array $data)
    {
        $this->orm = $orm;

        $this->class = $class;

        $this->data = $data;
    }

    public function count()
    {
        return count($this->data);
    }

    //-------------------------------------------------

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

        if (is_object($data))
        {
            return $data;
        }

        $class = $this->class;

        return $this->data[$this->position] = new $class($data, true, $this->orm);
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
}