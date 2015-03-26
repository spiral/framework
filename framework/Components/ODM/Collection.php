<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ODM;

use Psr\Http\Message\ServerRequestInterface;
use Spiral\Core\Component;
use Spiral\Support\Pagination\PaginableInterface;
use Spiral\Support\Pagination\PaginatorTrait;
use Spiral\Support\Pagination\Paginator;

/**
 * @method bool getSlaveOkay()
 * @method bool setSlaveOkay($slave_okay)
 * @method array getReadPreference()
 * @method bool setReadPreference($read_preference, $tags)
 * @method array drop()
 * @method array validate($validate)
 * @method bool|array insert($array_of_fields_OR_object, $options = array())
 * @method mixed batchInsert($documents, $options = array())
 * @method bool update($old_array_of_fields_OR_object, $new_array_of_fields_OR_object, $options = array())
 * @method bool|array remove($array_of_fields_OR_object, $options = array())
 * @method bool ensureIndex($key_OR_array_of_keys, $options)
 * @method array deleteIndex($string_OR_array_of_keys)
 * @method array deleteIndexes()
 * @method array getIndexInfo()
 * @method save($array_of_fields_OR_object, $options = array())
 * @method array createDBRef($array_with_id_fields_OR_MongoID)
 * @method array getDBRef($reference)
 * @method array group($keys_or_MongoCode, $initial_value, $array_OR_MongoCode, $options = array())
 * @method bool|array distinct($key, $query)
 * @method array aggregate(array $pipeline, array $op, array $pipelineOperators)
 */
class Collection extends Component implements \Iterator, PaginableInterface
{
    /**
     * Pagination.
     */
    use PaginatorTrait;

    /**
     * Sort order.
     *
     * @link http://php.net/manual/en/class.mongocollection.php#mongocollection.constants.ascending
     */
    const ASCENDING = 1;

    /**
     * Sort order.
     *
     * @link http://php.net/manual/en/class.mongocollection.php#mongocollection.constants.descending
     */
    const DESCENDING = -1;

    /**
     * Mongo collection name.
     *
     * @var string
     */
    protected $name = '';

    /**
     * Associated mongo database name/id.
     *
     * @var string
     */
    protected $database = 'default';

    /**
     * ODMManager component.
     *
     * @invisible
     * @var ODM
     */
    protected $odm = null;

    /**
     * Collection schema used to define classes used for documents and other operations.
     *
     * @var array
     */
    protected $schema = array();

    /**
     * Fields and conditions to query by.
     *
     * @link http://docs.mongodb.org/manual/tutorial/query-documents/
     * @var array
     */
    protected $query = array();

    /**
     * Active selection cursor.
     *
     * @var \MongoCursor
     */
    protected $cursor = null;

    /**
     * Fields to sort.
     *
     * @var array
     */
    protected $sort = array();

    /**
     * New ODM collection instance, ODM collection used to perform queries to MongoDatabase and
     * resolve correct document instance based on response.
     *
     * @link http://docs.mongodb.org/manual/tutorial/query-documents/
     * @param string $name     Collection name.
     * @param string $database Associated database name/id.
     * @param ODM    $odm      ODMManager component instance.
     * @param array  $query    Fields and conditions to query by.
     */
    public function __construct($name, $database, ODM $odm, array $query = array())
    {
        $this->name = $name;
        $this->database = $database;
        $this->odm = $odm;
        $this->query = $query;
    }

    /**
     * Mongo collection name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Associated mongo database name/id.
     *
     * @return string
     */
    public function getDatabaseName()
    {
        return $this->database;
    }

    /**
     * Current fields and conditions to query by.
     *
     * @link http://docs.mongodb.org/manual/tutorial/query-documents/
     * @return array
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Set additional query field, fields will be merged to currently existed request using array_merge.
     *
     * @link http://docs.mongodb.org/manual/tutorial/query-documents/
     * @param array $query Fields and conditions to query by.
     * @return static
     */
    public function query(array $query = array())
    {
        array_walk_recursive($query, function (&$value)
        {
            if ($value instanceof \DateTime)
            {
                //MongoDate is always UTC, which is good :)
                $value = new \MongoDate($value->getTimestamp());
            }
        });

        $this->query = array_merge($this->query, $query);
        if (!empty($this->cursor))
        {
            $this->cursor = null;
        }

        return $this;
    }

    /**
     * Set additional query field, fields will be merged to currently existed request using array_merge.
     *
     * @link http://docs.mongodb.org/manual/tutorial/query-documents/
     * @param array $query Fields and conditions to query by.
     * @return static
     */
    public function where(array $query = array())
    {
        return $this->query($query);
    }

    /**
     * Sorts the results by given fields.
     *
     * @link http://www.php.net/manual/en/mongocursor.sort.php
     * @param array $fields An array of fields by which to sort. Each element in the array has as
     *                      key the field name, and as value either 1 for ascending sort, or -1 for
     *                      descending sort.
     * @return static|Document[]
     */
    public function sort(array $fields)
    {
        $this->cursor && $this->cursor->sort($fields);
        $this->sort = $fields;

        return $this;
    }

    /**
     * Get associated mongo collection.
     *
     * @return \MongoCollection
     */
    protected function mongoCollection()
    {
        return $this->odm->db($this->database)->selectCollection($this->name);
    }

    /**
     * Perform query and get mongoDB cursor. Attention, mongo skip is not really optimal operation
     * on high amount of data.
     *
     * @param array $query  Fields and conditions to query by.
     * @param array $fields Fields of the results to return.
     * @return \MongoCursor
     */
    public function getCursor($query = array(), $fields = array())
    {
        if (!empty($this->cursor) && empty($query) && empty($fields))
        {
            //Nothing changed since last cursor request
            return $this->cursor;
        }

        //Updating query
        !empty($query) && $this->query($query);

        //Getting cursor from database
        $this->cursor = $this->mongoCollection()->find($this->query, $fields);

        $this->doPagination();

        //Sorting, limits and skipping
        $this->sort && $this->cursor->sort($this->sort);
        $this->limit && $this->cursor->limit($this->limit);
        $this->offset && $this->cursor->skip($this->offset);

        $queryInfo = array('query' => $this->query, 'sort' => $this->sort);
        if (!empty($this->limit))
        {
            $queryInfo['limit'] = (int)$this->limit;
        }

        if (!empty($this->offset))
        {
            $queryInfo['offset'] = (int)$this->offset;
        }

        //$this->logger()->debug("{database}/{collection}: " . json_encode($queryInfo), array(
        //    'collection' => $this->name,
        //    'database'   => $this->database,
        //    'queryInfo'  => $queryInfo
        //));

        return $this->cursor;
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
        if (empty($this->schema))
        {
            $this->schema = $this->odm->getSchema($this->database . '/' . $this->name);
            if (empty($this->schema))
            {
                throw new ODMException(
                    "Unable to find appropriate document class, "
                    . "no schema found for collection '{$this->database}/{$this->name}'."
                );
            }
        }

        $class = $this->odm->defineClass($fields, $this->schema[ODM::C_DEFINITION]);

        //No IoC here due unpredictable consequences
        return new $class($fields);
    }

    /**
     * Send collection query to fetch multiple ODM Documents.
     *
     * @param array $query Fields and conditions to query by.
     * @return static|Document[]
     */
    public function find(array $query = array())
    {
        $this->getCursor($query);

        return $this;
    }

    /**
     * Select one document or it's fields from collection.
     *
     * @param array $query  Fields and conditions to query by.
     * @param array $fields Fields of the results to return. If not provided Document object will be
     *                      returned.
     * @return Document|array
     */
    public function findOne(array $query = array(), array $fields = array())
    {
        if (!$document = $this->getCursor($query, $fields)->limit(1)->getNext())
        {
            return null;
        }

        //Resetting
        $this->cursor->reset();
        $this->cursor = null;

        return $fields ? $document : $this->createDocument($document);
    }

    /**
     * Fetch all available documents from query.
     *
     * @return Document[]
     */
    public function fetchDocuments()
    {
        //Ensure selection
        $this->getCursor();

        $result = array();
        foreach ($this as $document)
        {
            $result[] = $document;
        }

        return $result;
    }

    /**
     * Fetch all available documents as array.
     *
     * @param array $fields Fields of the results to return.
     * @return array
     */
    public function fetchFields($fields = array())
    {
        $result = array();
        foreach ($this->getCursor(array(), $fields) as $document)
        {
            $result[] = $document;
        }

        return $result;
    }

    /**
     * Limits the number of results returned.
     *
     * @link http://www.php.net/manual/en/mongocursor.limit.php
     * @param int $limit The number of results to return.
     * @return static|Document[]
     */
    public function limit($limit = 0)
    {
        !empty($this->cursor) && $this->cursor->limit($limit);
        $this->limit = $limit;

        return $this;
    }

    /**
     * Skips a number of results.
     *
     * @link http://www.php.net/manual/en/mongocursor.skip.php
     * @param int $offset The number of results to skip.
     * @return static|Document[]
     */
    public function offset($offset = 0)
    {
        !empty($this->cursor) && $this->cursor->skip($this->offset);
        $this->offset = $offset;

        return $this;
    }

    /**
     * Return the current document.
     *
     * @link http://www.php.net/manual/en/mongocursor.current.php
     * @link http://php.net/manual/en/iterator.current.php
     * @return Document
     */
    public function current()
    {
        $document = $this->getCursor()->current();

        return $document ? $this->createDocument($document) : null;
    }

    /**
     * Checks if there are any more elements in this cursor
     *
     * @link http://www.php.net/manual/en/mongocursor.hasnext.php
     * @return bool
     */
    public function hasNext()
    {
        return $this->getCursor()->hasNext();
    }

    /**
     * Advances the cursor to the next result.
     *
     * @link http://www.php.net/manual/en/mongocursor.next.php
     */
    public function next()
    {
        $this->getCursor()->next();
    }

    /**
     * Returns the current result's _id (as string).
     *
     * @link http://www.php.net/manual/en/mongocursor.key.php
     * @return string
     */
    public function key()
    {
        return $this->getCursor()->key();
    }

    /**
     * Checks if the cursor is reading a valid result.
     *
     * @link http://www.php.net/manual/en/mongocursor.valid.php
     * @return bool
     */
    public function valid()
    {
        return $this->getCursor()->valid();
    }

    /**
     * Returns the cursor to the beginning of the result set.
     *
     * @link http://php.net/manual/en/mongocursor.rewind.php
     */
    public function rewind()
    {
        $this->getCursor()->rewind();
    }

    /**
     * Paginate current selection.
     *
     * @param int                    $limit         Pagination limit.
     * @param string                 $pageParameter Name of parameter in request query which is used
     *                                              to store the current page number. "page" by default.
     * @param int                    $count         Forced count value, if 0 paginator will try to fetch
     *                                              count from associated object.
     * @param ServerRequestInterface $request       Dispatcher request.
     * @return mixed
     * @throws ODMException
     */
    public function paginate($limit = 50, $pageParameter = 'page', $count = 0, $request = null)
    {
        if (!empty($this->cursor))
        {
            throw new ODMException("Selection has to be paginated before cursor creation.");
        }

        $this->paginator = Paginator::make(
            compact('pageParameter') + ($request ? compact('request') : array())
        );

        $this->paginator->setLimit($limit);
        $this->paginationCount = $count;

        return $this;
    }

    /**
     * Counts the number of results for this query.
     *
     * @link http://www.php.net/manual/en/mongocursor.count.php
     * @param bool $all If false limit and offset will be included to query.
     * @return int
     */
    public function count($all = true)
    {
        return $this->getCursor()->count(!$all);
    }

    /**
     * Bypass call to MongoCollection.
     *
     * @param string $method    Method name.
     * @param array  $arguments Method arguments.
     * @return mixed
     */
    public function __call($method, array $arguments = array())
    {
        return call_user_func_array(array($this->mongoCollection(), $method), $arguments);
    }

    /**
     * Destructing.
     */
    public function __destruct()
    {
        !empty($this->cursor) && $this->cursor->reset();
        $this->cursor = $this->odm = $this->paginator = null;
        $this->query = array();
    }

    /**
     * Simplified way to dump information.
     *
     * @return Object
     */
    public function __debugInfo()
    {
        return (object)array(
            'collection' => $this->database . '/' . $this->name,
            'query'      => $this->query,
            'limit'      => $this->limit,
            'offset'     => $this->offset,
            'sort'       => $this->sort
        );
    }
}