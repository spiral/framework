<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ODM;

use Spiral\Components\ODM\Collection\CursorReader;
use Spiral\Core\Component;
use Spiral\Support\Pagination\PaginableInterface;
use Spiral\Support\Pagination\PaginatorTrait;

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
class Collection extends Component implements \IteratorAggregate, PaginableInterface
{
    /**
     * Pagination and logging traits.
     */
    use PaginatorTrait, Component\LoggerTrait;

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
     * ODM component.
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
     * MongoDatabase instance.
     *
     * @return MongoDatabase
     */
    public function mongoDatabase()
    {
        return $this->odm->db($this->database);
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
     * @return static
     */
    public function sort(array $fields)
    {
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
        return $this->mongoDatabase()->selectCollection($this->name);
    }

    /**
     * Perform query and get mongoDB cursor. Attention, mongo skip is not really optimal operation
     * on high amount of data.
     *
     * @param array $query       Fields and conditions to query by.
     * @param array $fields      Fields of the results to return.
     * @param bool  $plainResult If true no documents to will be created.
     * @return \MongoCursor
     */
    public function createCursor($query = array(), $fields = array(), $plainResult = false)
    {
        $this->query($query);
        $this->doPagination();

        $cursorReader = new CursorReader(
            $this->mongoCollection()->find($this->query, $fields),
            $this->odm,
            !empty($fields) || $plainResult
                ? array()
                : $this->odm->getSchema($this->database . '/' . $this->name),
            $this->sort,
            $this->limit,
            $this->offset
        );

        if ((!empty($this->limit) || !empty($this->offset)) && empty($this->sort))
        {
            self::logger()->warning(
                "MongoDB query executed with limit/offset but without specified sorting."
            );
        }

        if (!$this->mongoDatabase()->isProfiling())
        {
            return $cursorReader;
        }

        $queryInfo = array(
            'query' => $this->query,
            'sort'  => $this->sort
        );

        if (!empty($this->limit))
        {
            $queryInfo['limit'] = (int)$this->limit;
        }

        if (!empty($this->offset))
        {
            $queryInfo['offset'] = (int)$this->offset;
        }

        if ($this->mongoDatabase()->getProfilingLevel() == MongoDatabase::PROFILE_EXPLAIN)
        {
            $queryInfo['explained'] = $cursorReader->explain();
        }

        self::logger()->debug(
            "{database}/{collection}: " . json_encode($queryInfo, JSON_PRETTY_PRINT),
            array(
                'collection' => $this->name,
                'database'   => $this->database,
                'queryInfo'  => $queryInfo
            ));

        return $cursorReader;
    }

    /**
     * Send collection query to fetch multiple ODM Documents.
     *
     * @param array $query Fields and conditions to query by.
     * @return static
     */
    public function find(array $query = array())
    {
        return $this->query($query);
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
        return $this->createCursor($query, $fields)->limit(1)->getNext();
    }

    /**
     * Fetch all available documents from query.
     *
     * @return Document[]
     */
    public function fetchDocuments()
    {
        $result = array();
        foreach ($this->createCursor() as $document)
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
        foreach ($this->createCursor(array(), $fields, true) as $document)
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
     * @return static
     */
    public function limit($limit = 0)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * Skips a number of results.
     *
     * @link http://www.php.net/manual/en/mongocursor.skip.php
     * @param int $offset The number of results to skip.
     * @return static
     */
    public function offset($offset = 0)
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * Counts all matched documents.
     *
     * @link http://docs.mongodb.org/manual/reference/method/db.collection.count/
     * @return int
     */
    public function count()
    {
        return $this->mongoCollection()->count($this->query);
    }

    /**
     * Retrieve an external iterator, SelectBuilder will return QueryResult as iterator.
     *
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return CursorReader|Document[]
     */
    public function getIterator()
    {
        return $this->createCursor();
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
        $this->odm = $this->paginator = null;
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