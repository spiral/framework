<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ODM\Collection;
use Spiral\Components\ODM\ODM;
use Spiral\Components\ODM\Document;
use Spiral\Components\ODM\ODMException;

/**
 * @method array explain()
 */
class CursorReader implements \Iterator
{
    /**
     * MongoCursor instance.
     *
     * @var \MongoCursor
     */
    protected $cursor = null;

    /**
     * ODM component.
     *
     * @var ODM
     */
    protected $odm = null;

    /**
     * Document schema used by CursorReader to construct valid documents. Fields will be returned
     * if no schema provided.
     *
     * @var array
     */
    protected $schema = array();

    /**
     * CursorReader is wrapper at top of MongoCursor used to correctly resolve data type of result.
     *
     * @param \MongoCursor $cursor
     * @param ODM          $odm
     * @param array        $schema
     * @param array        $sort
     * @param int          $limit
     * @param int          $offset
     */
    public function __construct(
        \MongoCursor $cursor,
        ODM $odm,
        array $schema,
        array $sort = array(),
        $limit = null,
        $offset = null
    )
    {
        $this->cursor = $cursor;
        $this->odm = $odm;
        $this->schema = $schema;

        if (!empty($sort))
        {
            $this->cursor->sort($sort);
        }

        !empty($limit) && $this->cursor->limit($limit);
        !empty($offset) && $this->cursor->skip($offset);
    }

    /**
     * Create document instance by class definition stored in ODM schema.
     *
     * @param array $fields
     * @return Document
     * @throws ODMException
     */
    protected function createDocument(array $fields)
    {
        $class = $this->odm->defineClass($fields, $this->schema[ODM::C_DEFINITION]);

        //No IoC here due unpredictable consequences
        return new $class($fields, null, array(), $this->odm);
    }

    /**
     * Sets the fields for a query.
     *
     * @link http://www.php.net/manual/en/mongocursor.fields.php
     * @param array $fields Fields to return (or not return).
     * @throws \MongoCursorException
     * @return static
     */
    public function fields(array $fields)
    {
        $this->cursor->fields($fields);
        $this->schema = array();

        return $this;
    }

    /**
     * Return the current document.
     *
     * @link http://www.php.net/manual/en/mongocursor.current.php
     * @link http://php.net/manual/en/iterator.current.php
     * @return Document|array
     */
    public function current()
    {
        $document = $this->cursor->current();
        if (empty($this->schema))
        {
            return $document;
        }

        return $document ? $this->createDocument($document) : null;
    }

    /**
     * Return the next object to which this cursor points, and advance the cursor
     *
     * @link http://www.php.net/manual/en/mongocursor.getnext.php
     * @throws \MongoConnectionException
     * @throws \MongoCursorTimeoutException
     * @return array Returns the next object
     */
    public function getNext()
    {
        $this->cursor->next();

        return $this->current();
    }

    /**
     * Advances the cursor to the next result.
     *
     * @link http://www.php.net/manual/en/mongocursor.next.php
     */
    public function next()
    {
        $this->cursor->next();
    }

    /**
     * Returns the current result's _id (as string).
     *
     * @link http://www.php.net/manual/en/mongocursor.key.php
     * @return string
     */
    public function key()
    {
        return $this->cursor->key();
    }

    /**
     * Checks if the cursor is reading a valid result.
     *
     * @link http://www.php.net/manual/en/mongocursor.valid.php
     * @return bool
     */
    public function valid()
    {
        return $this->cursor->valid();
    }

    /**
     * Returns the cursor to the beginning of the result set.
     *
     * @link http://php.net/manual/en/mongocursor.rewind.php
     */
    public function rewind()
    {
        $this->cursor->rewind();
    }

    /**
     * Forwarding call to cursor.
     *
     * @param string $method
     * @param array  $arguments
     * @return mixed
     */
    public function __call($method, array $arguments)
    {
        $result = call_user_func_array(array($this->cursor, $method), $arguments);
        if ($result === $this->cursor)
        {
            return $this;
        }

        return $result;
    }
}