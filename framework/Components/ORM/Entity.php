<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ORM;

use Spiral\Components\DBAL\DatabaseManager;
use Spiral\Components\DBAL\Table;
use Spiral\Components\I18n\Translator;
use Spiral\Core\Events\EventDispatcher;
use Spiral\Support\Models\AccessorInterface;
use Spiral\Support\Models\DatabaseEntityInterface;
use Spiral\Support\Models\DataEntity;
use Spiral\Support\Validation\Validator;

abstract class Entity extends DataEntity implements DatabaseEntityInterface
{
    /**
     * Set this constant to false to disable automatic column, index and foreign keys creation.
     * By default entities will read schema from database, so you can connect your ORM model to
     * already existed table.
     */
    const ACTIVE_SCHEMA = true;

    /**
     * Model specific constant to indicate that model has to be validated while saving. You still can
     * change this behaviour manually by providing argument to save method.
     */
    const FORCE_VALIDATION = true;

    /**
     * TODO EXAMPLES AND DESCRIPTIONS!!!!
     */
    const HAS_ONE            = 101;
    const HAS_MANY           = 102;
    const BELONGS_TO         = 103;
    const MANY_TO_MANY       = 104;
    const MANY_THOUGHT       = 105;
    const BELONGS_TO_MORPHED = 108;
    const MANY_TO_MORPHED    = 109;

    /**
     * Constants used to declare relation schemas.
     */
    const OUTER_KEY         = 901;
    const INNER_KEY         = 902;
    const MORPH_KEY         = 903;
    const THOUGHT_TABLE     = 904;
    const PIVOT_TABLE       = 904;
    const VIA_TABLE         = 904;
    const THOUGHT_INNER_KEY = 905;
    const THOUGHT_OUTER_KEY = 906;
    const WHERE             = 907;
    const WHERE_PIVOT       = 908;
    const PREFILL_FIELDS    = 909;
    const PREFILL_PIVOT     = 910;

    /**
     * Additional constants used to control relation schema creation.
     */
    const BACK_REF          = 1001;
    const CONSTRAINT        = 1002;
    const CONSTRAINT_ACTION = 1003;
    const CREATE_PIVOT      = 1004;
    const NULLABLE          = 1005;

    /**
     * Constants used to declare index type. See documentation for indexes property.
     */
    const INDEX  = 1000;
    const UNIQUE = 2000;

    /**
     * Already fetched schemas from ORM. Yes, ORM entity is really similar to ODM. Original ORM was
     * written long time ago before ODM and solutions i put to ORM was later used for ODM, while
     * "great transition" (tm) ODM was significantly updated and now ODM drive updates for ORM,
     * the student become the teacher.
     *
     * @var array
     */
    protected static $schemaCache = array();

    /**
     * Table associated with entity. Spiral will guess table name automatically based on class name
     * use Doctrine Inflector, however i'm STRONGLY recommend to declare table name manually as it
     * gives more readable code.
     *
     * @var string
     */
    protected $table = null;

    /**
     * Database name/id where entity table located in. By default database will be used if nothing
     * else is specified.
     *
     * @var string
     */
    protected $database = 'default';

    /**
     * TODO: DOCS
     *
     * @var array
     */
    protected $schema = array();

    /**
     * TODO: DOCS
     *
     * @var array
     */
    protected $indexes = array();

    /**
     * Default values associated with entity fields. This default values will be combined with values
     * fetched from table schema.
     *
     * @var array
     */
    protected $defaults = array();

    /**
     * Entity marked with solid state flag will be saved entirely without generating simplified update
     * operations with only changed fields.
     *
     * @var bool
     */
    protected $solidState = false;

    /**
     * List of updated fields associated with their original values.
     *
     * @var array
     */
    protected $updates = array();

    /**
     * Constructed set of relations.
     *
     * @var Relation[]
     */
    protected $relations = array();

    //todo: get fields should not return pre-cached results

    public function __construct($data = array())
    {
        if (!isset(self::$schemaCache[$class = get_class($this)]))
        {
            static::initialize();
            self::$schemaCache[$class] = ORM::getInstance()->getSchema(get_class($this));
        }

        //Prepared document schema
        $this->schema = self::$schemaCache[$class];

        //Merging with default values
        $this->fields = $data + $this->schema[ORM::E_DEFAULTS];

        if ((!$this->primaryKey()) || !is_array($data))
        {
            $this->solidState(true)->validationRequired = true;
        }
    }

    /**
     * Change entity solid state flag value. Entity marked with solid state flag will be saved
     * entirely without generating simplified update operations with only changed fields.
     *
     * @param bool $solidState  Solid state flag value.
     * @param bool $forceUpdate Mark all fields as changed to force update later.
     * @return static
     */
    public function solidState($solidState, $forceUpdate = false)
    {
        $this->solidState = $solidState;

        if ($forceUpdate)
        {
            $this->updates = $this->schema[ORM::E_DEFAULTS];
        }

        return $this;
    }

    /**
     * Get document primary key (_id) value. This value can be used to identify if model loaded from
     * databases or just created.
     *
     * @return mixed
     */
    public function primaryKey()
    {
        return isset($this->fields[$this->schema[ORM::E_PRIMARY_KEY]])
            ? $this->fields[$this->schema[ORM::E_PRIMARY_KEY]]
            : null;
    }

    /**
     * Is model were fetched from databases or recently created? Usually checks primary key value.
     *
     * @return bool
     */
    public function isLoaded()
    {
        return (bool)$this->primaryKey();
    }

    /**
     * Table name associated with entity.
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Database name/id associated with entity.
     *
     * @return string
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * Get mutator for specified field. Setters, getters and accessors can be retrieved using this
     * method.
     *
     * @param string $field   Field name.
     * @param string $mutator Mutator type (setters, getters, accessors).
     * @return mixed|null
     */
    protected function getMutator($field, $mutator)
    {
        if (isset($this->schema[ORM::E_MUTATORS][$mutator][$field]))
        {
            $mutator = $this->schema[ORM::E_MUTATORS][$mutator][$field];

            if (is_string($mutator) && isset(self::$mutatorAliases[$mutator]))
            {
                return self::$mutatorAliases[$mutator];
            }

            return $mutator;
        }

        return null;
    }

    /**
     * Check if field assignable.
     *
     * @param string $field
     * @return bool
     */
    protected function isFillable($field)
    {
        //Better replace it with isset later
        return !in_array($field, $this->schema[ORM::E_SECURED]) &&
        !(
            $this->schema[ORM::E_FILLABLE]
            && !in_array($field, $this->schema[ORM::E_FILLABLE])
        );
    }

    /**
     * Set value to one of field. Setter filter can be disabled by providing last argument.
     *
     * @param string $name   Field name.
     * @param mixed  $value  Value to set.
     * @param bool   $filter If false no filter will be applied.
     */
    public function setField($name, $value, $filter = true)
    {
        if (!array_key_exists($name, $this->fields))
        {
            //TODO: Check relations
            throw new ORMException("Undefined field '{$name}'.");
        }

        $original = isset($this->fields[$name]) ? $this->fields[$name] : null;
        parent::setField($name, $value, $filter);

        if (!array_key_exists($name, $this->updates))
        {
            $this->updates[$name] = $original instanceof AccessorInterface
                ? $original->serializeData()
                : $original;
        }
    }

    /**
     * Get array of changed or created fields for specified Entity or accessor.
     *
     * @return array
     */
    protected function compileUpdates()
    {
        if (!$this->hasUpdates() && !$this->solidState)
        {
            return array();
        }

        $updates = array();
        foreach ($this->fields as $name => $field)
        {
            if ($field instanceof ORMAccessor && ($this->solidState || $field->hasUpdates()))
            {
                $updates[$name] = $field->compileUpdates($name);
                continue;
            }

            if (!$this->solidState && !isset($this->updates[$name]))
            {
                continue;
            }

            if ($field instanceof ORMAccessor)
            {
                $field = $field->serializeData();
            }

            $updates[$name] = $field;
        }

        //Primary key should present in update set
        unset($updates[$this->schema[ORM::E_PRIMARY_KEY]]);

        return $updates;
    }

    /**
     * Check if entity or specific field is updated.
     *
     * @param string $field
     * @return bool
     */
    public function hasUpdates($field = null)
    {
        if (empty($field))
        {
            if (!empty($this->updates))
            {
                return true;
            }

            foreach ($this->fields as $field => $value)
            {
                if ($value instanceof ORMAccessor && $value->hasUpdates())
                {
                    return true;
                }
            }

            return false;
        }

        if (array_key_exists($field, $this->updates))
        {
            return true;
        }

        return false;
    }

    /**
     * Mark object as successfully updated and flush all existed atomic operations and updates.
     */
    public function flushUpdates()
    {
        $this->updates = array();

        foreach ($this->fields as $value)
        {
            if ($value instanceof ORMAccessor)
            {
                $value->flushUpdates();
            }
        }
    }

    /**
     * Get all non secured model fields. Additional processing can be applied to fields here.
     *
     * @return array
     */
    public function publicFields()
    {
        $fields = $this->getFields();
        foreach ($this->schema[ORM::E_HIDDEN] as $secured)
        {
            unset($fields[$secured]);
        }

        return $this->event('publicFields', $fields);
    }

    /**
     * Validator instance associated with model, will be response for validations of validation errors.
     * Model related error localization should happen in model itself.
     *
     * @return Validator
     */
    public function getValidator()
    {
        if (!empty($this->validator))
        {
            //Refreshing data
            return $this->validator->setData($this->fields);
        }

        return $this->validator = Validator::make(array(
            'data'      => $this->fields,
            'validates' => $this->schema[ORM::E_VALIDATES]
        ));
    }

    /**
     * Get all validation errors with applied localization using i18n component (if specified), any
     * error message can be localized by using [[ ]] around it. Data will be automatically validated
     * while calling this method (if not validated before).
     *
     * @param bool $reset Remove all model messages and reset validation, false by default.
     * @return array
     */
    public function getErrors($reset = false)
    {
        $this->validate();
        $errors = array();
        foreach ($this->errors as $field => $error)
        {
            if (
                is_string($error)
                && substr($error, 0, 2) == Translator::I18N_PREFIX
                && substr($error, -2) == Translator::I18N_POSTFIX
            )
            {
                if (isset($this->schema[ORM::E_MESSAGES][$error]))
                {
                    //Parent message
                    $error = Translator::getInstance()->get(
                        $this->schema[ORM::E_MESSAGES][$error],
                        substr($error, 2, -2)
                    );
                }
                else
                {
                    $error = $this->i18nMessage($error);
                }
            }

            $errors[$field] = $error;
        }

        if ($reset)
        {
            $this->errors = array();
        }

        return $errors;
    }

    /**
     * Get instance of DBAL\Table associated with specified entity.
     *
     * @param array           $schema Forced document schema.
     * @param DatabaseManager $dbal   DatabaseManager instance.
     * @return Table
     */
    public static function dbalTable(array $schema = array(), DatabaseManager $dbal = null)
    {
        $orm = ORM::getInstance();
        $dbal = !empty($dbal) ? $dbal : DatabaseManager::getInstance();

        $schema = !empty($schema) ? $schema : $orm->getSchema(get_called_class());

        static::initialize();

        $table = $dbal->db($schema[ORM::E_DB])->table($schema[ORM::E_TABLE]);
        if (isset(EventDispatcher::$dispatchers[static::getAlias()]))
        {
            return self::dispatcher()->fire('dbalTable', $table);
        }

        return $table;
    }

    /**
     * Get associated orm Selector. Selectors used to build complex related queries and fetch
     * models from database.
     *
     * @param array $schema
     * @return Selector
     */
    public static function ormSelector(array $schema = array())
    {
        $orm = ORM::getInstance();
        $schema = !empty($schema) ? $schema : $orm->getSchema(get_called_class());

        static::initialize();

        $selector = Selector::make(array(
            'schema'   => $schema,
            'database' => DatabaseManager::getInstance()->db($schema[ORM::E_DB]),
            'orm'      => $orm
        ));

        if (isset(EventDispatcher::$dispatchers[static::getAlias()]))
        {
            return self::dispatcher()->fire('selector', $selector);
        }

        return $selector;
    }

    /**
     * Save entity fields to associated table. Model has to be valid to be saved, in
     * other scenario method will return false, model errors can be found in getErrors() method.
     *
     * Events: saving, saved, updating, updated will be fired.
     *
     * @param bool $validate Validate entity fields before saving, enabled by default. Turning this
     *                       option off will increase performance but will make saving less secure.
     *                       You can use it when model data was not modified directly by user. By
     *                       default value is null which will force document to select behaviour
     *                       from FORCE_VALIDATION constant.
     * @return bool
     * @throws ORMException
     */
    public function save($validate = null)
    {
        if (is_null($validate))
        {
            $validate = static::FORCE_VALIDATION;
        }

        if ($validate && !$this->isValid())
        {
            return false;
        }

        //Primary key field name
        $primaryKey = $this->schema[ORM::E_PRIMARY_KEY];
        if (!$this->isLoaded())
        {
            $this->event('saving');

            //We will need to support models with primary keys in future
            unset($this->fields[$primaryKey]);

            $this->fields[$primaryKey] = static::dbalTable()->insert(
                $this->fields = $this->serializeData()
            );

            $this->event('saved');
        }
        elseif ($this->solidState || $this->hasUpdates())
        {
            $this->event('updating');

            static::dbalTable()->update(
                $this->compileUpdates(),
                array($primaryKey => $this->primaryKey())
            )->run();

            $this->event('updated');
        }

        $this->flushUpdates();

        return true;
    }

    /**
     * Delete entity from database.
     *
     * Events: deleting, deleted will be raised.
     */
    public function delete()
    {
        $this->event('deleting');
        $this->primaryKey() && static::dbalTable($this->schema)->delete(array(
            $this->schema[ORM::E_PRIMARY_KEY] => $this->primaryKey()
        ));

        $this->fields = $this->schema[ORM::E_DEFAULTS];
        $this->event('deleted');
    }

    /**
     * Create new model and set it's fields, all field values will be passed thought model filters
     * to ensure their type. Events: created
     *
     * You have to save model by yourself!
     *
     * @param array $fields Model fields to set, will be passed thought filters.
     * @return static
     */
    public static function create($fields = array())
    {
        $class = new static();

        //Forcing validation (empty set of fields is not valid set of fields)
        $class->validationRequired = true;
        $class->setFields($fields)->event('created');

        return $class;
    }

    /**
     * Get default search scope.
     *
     * @param array $scope
     * @return array
     */
    protected static function getScope($scope = array())
    {
        static::initialize();
        if (isset(EventDispatcher::$dispatchers[static::getAlias()]))
        {
            $scope = self::dispatcher()->fire('scope', array(
                'scope' => $scope,
                'model' => get_called_class()
            ))['scope'];
        }

        return $scope;
    }

    /**
     * Get ORM selector used to build complex SQL queries to fetch model and it's relations.
     *
     * @param mixed $query Fields and conditions to filter by.
     * @return Selector|static[]
     */
    public static function find(array $query = array())
    {
        return static::ormSelector()->query(self::getScope($query));
    }

    /**
     * Select one entity from collection.
     *
     * TODO: Add WITH parameter
     *
     * @param array $query Fields and conditions to filter by.
     * @return static
     */
    public static function findOne(array $query = array())
    {
        return static::find($query)->findOne();
    }

    public static function findByID($id = null)
    {
        //TODO: implement
        // return static::findOne(array('_id' => $id));
    }

    /**
     * Simplified way to dump information.
     *
     * @return Object
     */
    public function __debugInfo()
    {
        return (object)array(
            'table'  => $this->database . '/' . $this->table,
            'fields' => $this->getFields(),
            'errors' => $this->getErrors()
        );
    }
}