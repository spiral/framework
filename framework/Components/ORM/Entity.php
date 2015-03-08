<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ORM;

use Spiral\Components\ORM\Schemas\EntitySchema;
use Spiral\Support\Models\DatabaseEntityInterface;
use Spiral\Support\Models\DataEntity;

abstract class Entity extends DataEntity// implements DatabaseEntityInterface
{
    /**
     * ORM requested schema analysis.
     */
    const SCHEMA_ANALYSIS = 788;

    /**
     * Set this constant to false to disable automatic column and foreign keys creation. Using this flag is useful while
     * using Spiral ORM with already existed databases (you don't need to list columns which are already exists in table).
     */
    const ACTIVE_SCHEMA = true;

    /**
     * Model specific constant to indicate that model has to be validated while saving. You still can change this behaviour
     * manually by providing argument to save method.
     */
    const FORCE_VALIDATION = true;


    const HAS_ONE             = 1098;
    const HAS_MANY            = 2098;
    const BELONGS_TO          = 3323;
    const MANY_TO_MANY        = 4342;
    const MANY_THOUGHT        = 5344;
    const POLYMORPHIC         = 6342;
    const MANY_TO_POLYMORPHIC = 9234;
    const POLYMORPHIC_TO_MANY = 1430;

    /**
     * index constants
     */
    const INDEXES        = 0;
    const UNIQUE_INDEXES = 1;

    /**
     * Already fetched schemas from ORM. Yes, ORM entity is really similar to ODM. Original ORM was written long time ago
     * before ODM and solutions i put to ORM was later used for ODM, while "great transition" (tm) ODM was significantly
     * updated and now ODM drives updates for ORM, the student become the teacher.
     *
     * @var array
     */
    protected static $schemaCache = array();

    /**
     * Table associated with entity. At this right moment ORM will require you to specify table as i don't really like
     * pluralizers and etc. In future we can add some trait to resolved it for really lazy developers.
     *
     * @var string
     */
    protected $table = null;

    /**
     * Database name/id where entity table located in. By default default database will be used.
     *
     * @var string
     */
    protected $database = 'default';

    /**
     * Entity schema includes declaration of table columns (not required as you can link object to existed table) and
     * object relationships. Column definitions should be provided in soft form.  You can use DBAL abstract types to
     * declare your columns. Some types will be automatically associated with mutators while schema parsing.
     *
     * Example:
     * protected $schema = array(
     *      'id'           => 'primary',
     *      'status'       => 'enum(pending, active, disabled)',
     *      'name'         => 'string,nullable',
     *      'email'        => 'string(255)',
     *      'balance'      => 'decimal(10,2),nullable',
     *      'time_created' => 'timestamp',
     *      'description'  => 'text'
     * );
     *
     * Attention, schema is soft form of declaring table structure, this means you can create new columns, change their
     * types and etc, however renaming and removal are not possible using this form of declaration, you should to use
     * migrations for it. Make sure ACTIVE_SCHEMA is set to true.
     *
     * Schema can include relationships declaration, it's done using one of predefined constants, foreign model name
     * and other options. Check documentation near constants to see how they works.
     *
     * Example:
     * protected $schema = array(
     *      //Associated posts
     *      'posts'     => [self::MANY_TO_MANY => 'Models\Post'],
     *
     *      //Author
     *      'author'    => [self::BELONGS_TO => 'Models\User', 'author_id'],
     *
     *      //Attached comments
     *      'comments'  => [self::HAS_MANY => 'Model\Comment', self::POLYMORPHIC => 'target'],
     *
     *      //Photo tags
     *      'tags'      => [self::POLYMORPHIC_TO_MANY => 'Model\Tag', 'tagging']
     * );
     *
     * //Comment model schema
     *  protected $schema = array(
     *      //Author
     *      'author'    => [self::BELONGS_TO => 'Models\User', 'author_id'],
     *
     *      //Comment target (post, photo, user and etc...)
     *      'target'    => self::POLYMORPHIC
     * );
     *
     * //Tag model schema
     * protected $schema = array(
     *      'posts'  => [self::MANY_TO_POLYMORPHIC => 'Models\Post', 'tagging'],
     *      'photos' => [self::MANY_TO_POLYMORPHIC => 'Models\Photo', 'tagging'],
     *      'users'  => [self::MANY_TO_POLYMORPHIC => 'Models\User', 'tagging']
     * );
     *
     * @var array
     */
    protected $schema = array();

    /**
     * Default values to associated with newly created columns. Value should be automatically typecasted.
     *
     * Example:
     * protected $defaults = array(
     *      'status'  => 'pending',
     *      'balance' => 0
     * );
     *
     * Note: spiral will force default values for EVERY column, this means no SQL exception expected when inserting
     * partial content, however you have to write validation rules "in house".
     *
     * @var array
     */
    protected $defaults = array();

    /**
     * Set of indexes has to be created for associated table. You don't need to list primary key indexes here.
     *
     * Example:
     * protected $indexes = array(
     *      self::INDEXES        => array(
     *          ['status'], ['status', 'balance']
     *      ),
     *      self::UNIQUE_INDEXES => array(
     *          ['email']
     *      )
     * );
     *
     * Indexes will be inherited from parent model and merged together.
     *
     * @var array
     */
    protected $indexes = array(
        self::INDEXES        => array(),
        self::UNIQUE_INDEXES => array()
    );

    //    /**
    //     * Already loaded children.
    //     *
    //     * @var ORMObject[]
    //     */
    //    protected $children = array();

    /**
     * List of updated fields associated with their original values.
     *
     * @var array
     */
    protected $updates = array();

    //
    //    /**
    //     * Cached list of objects.
    //     *
    //     * @var array
    //     */
    //    static protected $idCache = array();

    //    /**
    //     * Documents marked with solid state flag will be saved entirely without generating separate atomic operations for each
    //     * field, instead one big set operation will be created. Your atomic() calls with be applied to document data but will not
    //     * be forwarded to collection.
    //     *
    //     * @var bool
    //     */
    //    protected $solidState = false;

    public function __construct($fields = array())
    {
        if (!isset(self::$schemaCache[$class = get_class($this)]))
        {
            static::initialize();
            //self::$schemaCache[$class] = ORM::getInstance()->getSchema(get_class($this));
        }

        //Prepared document schema
        //$this->schema = self::$schemaCache[$class];

        //Merging with default values
        $this->fields = $fields + $this->schema[ORM::E_DEFAULTS];
    }

    /**
     * Prepare entity property before caching it ORM schema. This method fire event "property" and sends SCHEMA_ANALYSIS
     * option to trait initializers. Method and even can be used to create custom columns, indexes and ect.
     *
     * @param EntitySchema $schema
     * @param string       $property Model property name.
     * @param mixed        $value    Model property value, will be provided in an inherited form.
     * @return mixed
     */
    public static function describeProperty(EntitySchema $schema, $property, $value)
    {
        static::initialize(self::SCHEMA_ANALYSIS);

        return static::dispatcher()->fire('describe', compact('schema', 'property', 'value'))['value'];
    }
}

//class ORMObject extends Model implements \ArrayAccess
//{
//
//    /**
//     * Constructing.
//     *
//     * @param array $fields
//     */
//    public function __construct(array $fields = array())
//    {
//        if (!isset(ORM::$schema[get_class($this)]))
//        {
//            return;
//        }
//
//        $this->schema = ORM::$schema[get_class($this)];
//
//        //Setting up column values
//        $this->fields = $fields + $this->schema[ORM::defaultValues];
//
//        //Custom and lambda filters initialization
//        $this->initialize();
//    }
//
//    /**
//     * Initialize custom filters.
//     */
//    protected function initialize()
//    {
//
//    }
//
//    /**
//     * Object table.
//     *
//     * @return string
//     */
//    public function tableName()
//    {
//        return $this->table;
//    }
//
//    /**
//     * Primary key.
//     *
//     * @return string
//     */
//    public function primaryKey()
//    {
//        return isset($this->fields[$this->schema[ORM::primaryKey]]) ? $this->fields[$this->schema[ORM::primaryKey]] : false;
//    }
//
//    /**
//     * Get associated array of object columns.
//     *
//     * @return array
//     */
//    public function getFields()
//    {
//        $result = $this->fields;
//        foreach ($result as $column => $value)
//        {
//            if ($value instanceof Column)
//            {
//                $result[$column] = $value->getValue();
//            }
//        }
//
//        return $result;
//    }
//
//    /**
//     * Set columns in bulk.
//     *
//     * @param array $columns
//     * @return ORMObject
//     */
//    public function setFields(array $columns)
//    {
//        unset($columns[$this->schema[ORM::primaryKey]]);
//        foreach ($columns as $name => $value)
//        {
//            if (array_key_exists($name, $this->fields))
//            {
//                $this->__set($name, $value);
//            }
//        }
//
//        return $this;
//    }
//
//    /**
//     * Is updated?
//     *
//     * @param string|null $column If null or false, all columns will be checked for updates.
//     * @return bool
//     */
//    public function hasUpdates($column = null)
//    {
//        if ($column)
//        {
//            return array_key_exists($column, $this->updates);
//        }
//
//        return (!empty($this->updates));
//    }
//
//    /**
//     * Force key updating.
//     *
//     * @param string $column
//     * @param bool   $isUpdated
//     * @param mixed  $previousValue
//     */
//    public function setUpdates($column, $isUpdated = true, $previousValue = null)
//    {
//        if (!$isUpdated)
//        {
//            unset($this->updates[$column]);
//        }
//        elseif (!array_key_exists($column, $this->updates))
//        {
//            $this->updates[$column] = $previousValue;
//        }
//    }
//
//    /**
//     * Column previous value.
//     *
//     * @param string $column
//     * @return bool
//     */
//    protected function previousValue($column)
//    {
//        if ($column && $this->hasUpdates($column))
//        {
//            return $this->updates[$column];
//        }
//
//        return null;
//    }
//
//    /**
//     * Is object data valid?
//     *
//     * @return bool
//     */
//    public function isValid()
//    {
//        return $this->validateData($this->fields);
//    }
//
//    /**
//     * Get: property, relationship, children.
//     *
//     * @param string $name
//     * @return mixed
//     * @throws ORMException
//     */
//    public function __get($name)
//    {
//        if (array_key_exists($name, $this->fields))
//        {
//            if (isset($this->schema[ORM::schema][$name]) && !($this->fields[$name] instanceof Column))
//            {
//                $class = $this->schema[ORM::schema][$name];
//                $this->fields[$name] = new $class($this, $name, isset($this->fields[$name]) ? $this->fields[$name] : null);
//            }
//
//            return $this->fields[$name];
//        }
//
//        if (array_key_exists($name, $this->children))
//        {
//            //Already fetched
//            return $this->children[$name];
//        }
//
//        if (isset($this->schema[ORM::schema][$name]))
//        {
//            $relationship = $this->schema[ORM::schema][$name];
//            $model = $relationship[ORM::rModel];
//
//            switch ($relationship[ORM::rType])
//            {
//                case ORM::oneToParent:
//                case ORM::manyToParent:
//                    return call_user_func(array($model, 'findByID'), $this->fields[$relationship[ORM::rVia]]);
//
//                case ORM::parentToOne:
//                    if (!$this->primaryKey() || !($this->children[$name] = call_user_func(array($model, 'findOne'), array($relationship[ORM::rVia] => $this->primaryKey()))))
//                    {
//                        //By reference
//                        $this->children[$name] = new $model(array($relationship[ORM::rVia] => &$this->fields[$this->schema[ORM::primaryKey]]), $this);
//                    }
//
//                    return $this->children[$name];
//            }
//        }
//
//        //Nothing to return
//        throw new ORMException("Invalid column or relationship '{$name}' in " . get_called_class() . ".");
//    }
//
//    /**
//     * Set: property, children, parent.
//     *
//     * @param string $name
//     * @param mixed  $value
//     * @throws ORMException
//     */
//    public function __set($name, $value)
//    {
//        if (array_key_exists($name, $this->fields))
//        {
//            if ($filter = $this->getFilter($name))
//            {
//                $value = call_user_func($filter, $value);
//            }
//
//            if (isset($this->schema[ORM::schema][$name]))
//            {
//                //Column accessor
//                return $this->__get($name)->setValue($value);
//            }
//
//            if (is_array($value))
//            {
//                //Nope
//                return;
//            }
//
//            if ($this->fields[$name] !== $value)
//            {
//                $this->updates[$name] = $this->fields[$name];
//                $this->fields[$name] = $value;
//            }
//
//            return;
//        }
//
//        if (isset($this->schema[ORM::schema][$name]))
//        {
//            $relationship = $this->schema[ORM::schema][$name];
//
//            if ($value->tableName() != $relationship[ORM::rModel])
//            {
//                throw new ORMException("Unable to attach '{$value->__toString()}' to '{$this->__toString()}', invalid object for this rule.");
//            }
//
//            switch ($relationship[ORM::rType])
//            {
//                case ORM::oneToParent:
//                case ORM::manyToParent:
//                    $this->__set($relationship[ORM::rVia], $value->primaryKey());
//                    break;
//
//                case ORM::parentToOne:
//                    $this->children[$name] = $value;
//                    $value->__set($relationship[ORM::rVia], $this->primaryKey());
//                    break;
//            }
//
//            return;
//        }
//
//        //Nothing to set
//        throw new ORMException("Invalid column or relationship '{$name}' in " . get_called_class() . ".");
//    }
//
//    /**
//     * Calling relationship.
//     *
//     * @param string $name
//     * @param array  $arguments
//     * @return mixed
//     * @throws ORMException
//     */
//    public function __call($name, $arguments)
//    {
//        $query = array();
//        if (isset($arguments[0]))
//        {
//            $query = $arguments[0];
//        }
//
//        if (isset($this->schema[ORM::schema][$name]))
//        {
//            $relationship = $this->schema[ORM::schema][$name];
//            $model = $relationship[ORM::rModel];
//
//            switch ($relationship[ORM::rType])
//            {
//
//                case ORM::parentToMany:
//                    return call_user_func(array($model, 'find'), array($relationship[ORM::rVia] => $this->primaryKey()) + $query);
//
//                case ORM::manyToMany:
//
//                    /**
//                     * @var Selector $selector
//                     */
//                    $selector = call_user_func(array($model, 'find'), $query);
//
//                    $selector->join($relationship[ORM::rMapTable])
//                        ->on($relationship[ORM::rForeignKey], $selector->tableName() . '.' . $relationship[ORM::rForeignPrimary])
//                        ->onValue($relationship[ORM::rLocalKey], $this->primaryKey());
//
//                    return $selector;
//            }
//        }
//
//        //Nothing to return
//        throw new ORMException("Invalid column or relationship '{$name}' in " . get_called_class() . ".");
//    }
//
//    /**
//     * Save/create ORM and children.
//     *
//     * @return bool|int
//     */
//    public function save()
//    {
//        //Validation
//        if (!$this->isValid())
//        {
//            return false;
//        }
//
//        $primaryKey = $this->schema[ORM::primaryKey];
//        if ($this->primaryKey())
//        {
//            foreach ($this->children as $children)
//            {
//                $children->save();
//            }
//
//            if ($this->hasUpdates())
//            {
//                //TODO: Column accessors for missfields
//                $update = array_intersect_key($this->fields, $this->updates);
//
//                //Updating
//                DBAL::update($this->table, $update)->where($primaryKey, '=', $this->primaryKey())->run();
//
//                foreach ($this->updates as $column => $value)
//                {
//                    if ($this->fields[$column] instanceof Column)
//                    {
//                        $this->fields[$column]->flushValue();
//                    }
//                }
//
//                $this->updates = array();
//            }
//
//            return $this->primaryKey();
//        }
//
//        //Creating (primary key will be saved)
//        $columns = $this->fields;
//        unset($columns[$primaryKey]);
//
//        $this->fields[$primaryKey] = DBAL::insert($this->table, $columns)->setPrimarySequence($this->schema[ORM::primarySequence])->run();
//        $this->updates = array();
//
//        foreach ($this->children as $children)
//        {
//            $children->save();
//        }
//
//        if ($this->hasUpdates())
//        {
//            return $this->save();
//        }
//
//        return $this->primaryKey();
//    }
//
//    /**
//     * Will remove ORMObject. Childnred objects has to be removed separatelly.
//     */
//    public function delete()
//    {
//        if (!$this->primaryKey())
//        {
//            return;
//        }
//
//        unset(self::$idCache[get_called_class() . $this->primaryKey()]);
//        DBAL::delete($this->table)->where($this->schema[ORM::primaryKey], '=', $this->primaryKey())->run();
//        $this->fields = $this->updates = $this->children = array();
//    }
//
//    /**
//     * Add many-to-many connection.
//     *
//     * @param string    $name
//     * @param ORMObject $object
//     * @param array     $mapData
//     * @return bool
//     */
//    public function link($name, ORMObject $object, array $mapData = array())
//    {
//        if (!$object->primaryKey())
//        {
//            return false;
//        }
//
//        if ($this->has($name, $object))
//        {
//            return false;
//        }
//
//        $relationship = $this->schema[ORM::schema][$name];
//
//        $mapData[$relationship[ORM::rLocalKey]] = $this->primaryKey();
//        $mapData[$relationship[ORM::rForeignKey]] = $object->primaryKey();
//
//        return (bool)DBAL::insert($relationship[ORM::rMapTable], $mapData)->run();
//    }
//
//    /**
//     * Disconnect many-to-many connection.
//     *
//     * @param string    $name
//     * @param ORMObject $object
//     * @return bool
//     */
//    public function unlink($name, ORMObject $object)
//    {
//        if (!$object->primaryKey())
//        {
//            return false;
//        }
//
//        if (!$this->has($name, $object))
//        {
//            return false;
//        }
//
//        $relationship = $this->schema[ORM::schema][$name];
//
//        return (bool)DBAL::delete($relationship[ORM::rMapTable])
//            ->where($relationship[ORM::rLocalKey], '=', $this->primaryKey())
//            ->where($relationship[ORM::rForeignKey], '=', $object->primaryKey())
//            ->run();
//    }
//
//    /**
//     * Check if objects was connected via many-to-many connection.
//     *
//     * @param string    $name
//     * @param ORMObject $object
//     * @return bool
//     */
//    public function has($name, ORMObject $object)
//    {
//        $relationship = $this->schema[ORM::schema][$name];
//
//        return (bool)DBAL::select($relationship[ORM::rLocalKey])->setTable($relationship[ORM::rMapTable])
//            ->where($relationship[ORM::rLocalKey], '=', $this->primaryKey())
//            ->where($relationship[ORM::rForeignKey], '=', $object->primaryKey())
//            ->setLimit(1)->run()->fetchColumn();
//    }
//
//    /**
//     * __toString
//     *
//     * @return string
//     */
//    public function __toString()
//    {
//        return get_class($this) . ($this->primaryKey() ? ' [' . $this->primaryKey() . ']' : ' [new]');
//    }
//
//    /**
//     * ArrayAccess.
//     *
//     * @param mixed $offset
//     * @return bool
//     */
//    public function offsetExists($offset)
//    {
//        return array_key_exists($offset, $this->fields);
//    }
//
//    /**
//     * ArrayAccess.
//     *
//     * @param mixed $offset
//     * @return mixed
//     */
//    public function offsetGet($offset)
//    {
//        return $this->__get($offset);
//    }
//
//    /**
//     * ArrayAccess.
//     *
//     * @param mixed $offset
//     * @param mixed $value
//     */
//    public function offsetSet($offset, $value)
//    {
//        $this->__set($offset, $value);
//    }
//
//    /**
//     * ArrayAccess.
//     *
//     * @param mixed $offset
//     */
//    public function offsetUnset($offset)
//    {
//        //Not supported
//    }
//
//    /**
//     * Alias for odmCollection.
//     *
//     * @param mixed $query
//     * @return Selector|ORMObject[]
//     */
//    static public function find($query = array())
//    {
//        return new Selector(get_called_class(), $query);
//    }
//
//    /**
//     * Alias for odmCollection.
//     *
//     * @param mixed $query
//     * @return ORMObject|null
//     */
//    static public function findOne($query = array())
//    {
//        return static::find($query)->findOne();
//    }
//
//    /**
//     * Alias for odmCollection.
//     *
//     * @param mixed $primaryKey
//     * @return ORMObject|null
//     */
//    static public function findByID($primaryKey = array())
//    {
//        if (!$primaryKey || !is_scalar($primaryKey))
//        {
//            return null;
//        }
//
//        $cacheID = get_called_class() . $primaryKey;
//        if (isset(self::$idCache[$cacheID]))
//        {
//            return self::$idCache[$cacheID];
//        }
//
//        return self::$idCache[$cacheID] = static::findOne(array('@primaryKey' => $primaryKey));
//    }
//
//    /**
//     * Saving object to cache by ID.
//     */
//    public function createIdentity()
//    {
//        self::$idCache[get_called_class() . $this->primaryKey()] = $this;
//    }
//
//    /**
//     * Model creation.
//     *
//     * @param array $fields
//     * @return ORMObject
//     */
//    static public function create($fields)
//    {
//        $model = get_called_class();
//        $model = new $model;
//
//        return $model->setFields($fields);
//    }
//}