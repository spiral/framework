<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\ORM;

use Spiral\Components\DBAL\Database;
use Spiral\Components\DBAL\Table;
use Spiral\Components\I18n\Translator;
use Spiral\Support\Models\AccessorInterface;
use Spiral\Support\Models\DatabaseEntityInterface;
use Spiral\Support\Models\DataEntity;
use Spiral\Support\Validation\Validator;

abstract class ActiveRecord extends DataEntity implements DatabaseEntityInterface
{
    /**
     * We are going to inherit parent validation, we have to let i18n indexer know to collect both
     * local and parent messages under one bundle.
     */
    const I18N_INHERIT_MESSAGES = true;

    /**
     * Set this constant to false to disable automatic column, index and foreign keys creation.
     *
     * By default entities will read schema from database, so you can connect your ORM model to
     * already existed table.
     *
     * Attention, orm update will fail if any external model requested changed in table linked to
     * ActiveRecord with ACTIVE_SCHEMA = false.
     */
    const ACTIVE_SCHEMA = true;

    /**
     * Model specific constant to indicate that model has to be validated while saving. You still can
     * change this behaviour manually by providing argument to save method.
     */
    const FORCE_VALIDATION = true;

    /**
     * Indication that model data was deleted.
     */
    const DELETED         = 900;

    /**
     * Model has one children model relation. Example: User has one profile.
     *
     * Example:
     * protected $schema = [
     *      ...,
     *      'profile' => [self::HAS_ONE => 'Models\Profile']
     * ];
     *
     * By default relation will create foreign column, key and index. Default inner key where model
     * is linked to - current model primary key name.
     *
     * Example (children model will be related to custom inner key).
     * protected $schema = [
     *      ...,
     *      'profile' => [
     *          self::HAS_ONE   => 'Models\Profile',
     *          self::INNER_KEY => 'internal_key'       //Column in "users" table
     *      ]
     * ];
     *
     * By default outer key will be named as parent_role_name_{inner_key} (Example: User model +
     * id as primary key will create outer key named "user_id"), we can redefine it:
     * protected $schema = [
     *      ...,
     *      'profile' => [
     *          self::HAS_ONE   => 'Models\Profile',
     *          self::OUTER_KEY => 'parent_user_id'     //Column in "profiles" table
     *      ]
     * ];
     *
     * To stop schema builder from creating foreign key we can use CONSTRAINT option:
     * protected $schema = [
     *      ...,
     *      'profile' => [
     *          self::HAS_ONE    => 'Models\Profile',
     *          self::CONSTRAINT => false               //No foreign keys will be created
     *      ]
     * ];
     *
     * In some cases we want to force custom constraint action for DELETE and UPDATE rules:
     * protected $schema = [
     *      ...,
     *      'profile' => [
     *          self::HAS_ONE           => 'Models\Profile',
     *          self::CONSTRAINT_ACTION => 'NO ACTION' //We can also use different types, but make
     *                                                 //sure they are supported by your database
     *      ]
     * ];
     *
     * To state that child model should always have defined parent let's set NULLABLE option to false.
     * Attention, this option can be applied ONLY while child table is empty! By default NULLABLE
     * flag states true:
     * protected $schema = [
     *      ...,
     *      'profile' => [
     *          self::HAS_ONE  => 'Models\Profile',
     *          self::NULLABLE => false                //Profile should always have associated user
     *      ]
     * ];
     *
     * Attention, while using NULLABLE relations you have to clearly state default value as NULL.
     *
     * You can always inverse this relation to create relation in child model:
     * protected $schema = [
     *      ...,
     *      'profile' => [
     *          self::HAS_ONE => 'Models\Profile',
     *          self::INVERSE => 'parent_user'        //Will create BELONGS_TO relation in Profile
     *                                                //model
     *      ]
     * ];
     *
     * By default this relation will be preloaded using INLOAD method (joined to query).
     */
    const HAS_ONE = 101;

    /**
     * Model has many models relation. Example: User has many posts.
     *
     * Example:
     * protected $schema = [
     *      ...,
     *      'posts' => [self::HAS_MANY => 'Models\Post']
     * ];
     *
     * By default relation will create foreign column, key and index. Default inner key where model
     * is linked to - current model primary key name.
     *
     * Example (children model will be related to custom inner key).
     * protected $schema = [
     *      ...,
     *      'posts' => [
     *          self::HAS_MANY  => 'Models\Post',
     *          self::INNER_KEY => 'internal_key'       //Column in "users" table
     *      ]
     * ];
     *
     * By default outer key will be named as parent_role_name_{inner_key} (Example: User model +
     * id as primary key will create outer key named "user_id"), we can redefine it:
     * protected $schema = [
     *      ...,
     *      'posts' => [
     *          self::HAS_MANY  => 'Models\Post',
     *          self::OUTER_KEY => 'parent_user_id'     //Column in "posts" table
     *      ]
     * ];
     *
     * To stop schema builder from creating foreign key we can use CONSTRAINT option:
     * protected $schema = [
     *      ...,
     *      'posts' => [
     *          self::HAS_MANY   => 'Models\Post',
     *          self::CONSTRAINT => false               //No foreign keys will be created
     *      ]
     * ];
     *
     * In some cases we want to force custom constraint action for DELETE and UPDATE rules:
     * protected $schema = [
     *      ...,
     *      'posts' => [
     *          self::HAS_MANY          => 'Models\Post',
     *          self::CONSTRAINT_ACTION => 'NO ACTION' //We can also use different types, but make
     *                                                 //sure they are supported by your database
     *      ]
     * ];
     *
     * To state that child model should always have defined parent let's set NULLABLE option to false.
     * Attention, this option can be applied ONLY while child table is empty! By default NULLABLE
     * flag states true:
     * protected $schema = [
     *      ...,
     *      'posts' => [
     *          self::HAS_MANY  => 'Models\Post',
     *          self::NULLABLE  => false                //Post should always have associated user
     *      ]
     * ];
     *
     * Attention, while using NULLABLE relations you have to clearly state default value as NULL.
     *
     * Has many relation allows you to define custom WHERE condition:
     * protected $schema = [
     *      ...,
     *      'publicPosts' => [
     *          self::HAS_MANY  => 'Models\Post',
     *          self::WHERE     => [
     *              '{table}.status' => 'public'      //Relates to only public posts, {table} is
     *                                                //required to be included into where condition
     *                                                //to build valid where or on where statement.
     *          ]
     *      ]
     * ];
     *
     * You can always inverse this relation to create relation in child model:
     * protected $schema = [
     *      ...,
     *      'posts' => [
     *          self::HAS_MANY => 'Models\Post',
     *          self::INVERSE => 'author'             //Will create BELONGS_TO relation in Post
     *                                                //model
     *      ]
     * ];
     *
     * Attention, WHERE conditions will be not be inversed!
     * You can also use MORPH_KEY option if you want to state that relation points to morphed
     * model (see BELONGS_TO morph section documentation):
     * protected $schema = [
     *      ...,
     *      'approvedComments' => [
     *          self::HAS_MANY   => 'Models\Comment',
     *          self::MORPH_KEY => 'target_type'
     *          self::WHERE     => [
     *              '{table}.status' => 'approved'    //Only approved model comments
     *          ]
     *      ]
     * ];
     *
     *
     * By default this relation will be preloaded using POSTLOAD method (executed as separate query).
     */
    const HAS_MANY = 102;

    /**
     * Model has one parent relation. Example: Post has author.
     *
     * Example:
     * protected $schema = [
     *      ...,
     *      'author' => [self::BELONGS_TO => 'Models\User']
     * ];
     *
     * By default relation will create foreign column, key and index in model associated table.
     * Default outer key where model is linked to - parent model primary key name.
     *
     * Example (children model will be related to custom parent key).
     * protected $schema = [
     *      ...,
     *      'author' => [
     *          self::BELONGS_TO   => 'Models\User',
     *          self::OUTER_KEY    => 'internal_key'       //Column in "users" table
     *      ]
     * ];
     *
     * By default outer key will be named as parent_model_role_name_{inner_key} (Example: User model
     * + id as primary key will create inner key named "user_id"), we can redefine it:
     * protected $schema = [
     *      ...,
     *      'author' => [
     *          self::BELONGS_TO   => 'Models\User',
     *          self::INNER_KEY    => 'parent_user_id'     //Column in "comments" table
     *      ]
     * ];
     *
     * To stop schema builder from creating foreign key we can use CONSTRAINT option:
     * protected $schema = [
     *      ...,
     *      'author' => [
     *          self::BELONGS_TO => 'Models\User',
     *          self::CONSTRAINT => false                  //No foreign keys will be created
     *      ]
     * ];
     *
     * In some cases we want to force custom constraint action for DELETE and UPDATE rules:
     * protected $schema = [
     *      ...,
     *      'author' => [
     *          self::BELONGS_TO        => 'Models\User',
     *          self::CONSTRAINT_ACTION => 'NO ACTION'    //We can also use different types, but make
     *                                                    //sure they are supported by your database
     *      ]
     * ];
     *
     * To state that child model should always have defined parent let's set NULLABLE option to false.
     * Attention, this option can be applied ONLY while child table is empty! By default NULLABLE
     * flag states true:
     *
     * protected $schema = [
     *      ...,
     *      'author' => [
     *          self::BELONGS_TO  => 'Models\User',
     *          self::NULLABLE    => false                //Post should always have associated user
     *      ]
     * ];
     *
     * Attention, while using NULLABLE relations you have to clearly state default value as NULL.
     *
     * You can always inverse this relation to create relation in child model, however you have to
     * specify inversed relation type (HAS_ONE or HAS_MANY) in this case.
     * protected $schema = [
     *      ...,
     *      'author' => [
     *          self::BELONGS_TO => 'Models\User',
     *          self::INVERSE => [self::HAS_MANY, 'posts'] //Will create HAS_MANY relation in User
     *                                                     //model
     *      ]
     * ];
     *
     * Tip, to simplify schema reading use relation constant from outer class (User::HAS_MANY),
     * example:
     * protected $schema = [
     *      ...,
     *      'author' => [
     *          self::BELONGS_TO => 'Models\User',
     *          self::INVERSE    => [User::HAS_MANY, 'posts'] //Will create HAS_MANY relation in
     *                                                        // User model
     *      ]
     * ];
     *
     * HAS_MANY relation can be used as polymorphic relation to multiple parents, relation definition
     * in this case should point to interface and not real model (let's use Comment model as
     * example):
     * protected $schema = [
     *      ...,
     *      'target' => [
     *          self::BELONGS_TO => 'Models\CommentableInterface'
     *      ]
     * ];
     *
     * By default morph key will be named as relation_name_type: "target_type".
     *
     * System will automatically create inner key and morph key to support such relation. You can
     * define custom morph key via MORPH_KEY option. The most efficient way to use polymorphic
     * relation is in combination with INVERSE option:
     * protected $schema = [
     *      ...,
     *      'target' => [
     *          self::BELONGS_TO => 'Models\CommentableInterface',
     *          self::INVERSE    => [self::HAS_MANY, 'comments'] //Will create "comments" HAS_MANY
     *                                                           //relation in every model which
     *                                                           //implemented CommentableInterface
     *      ]
     * ];
     *
     * By default this relation will be preloaded using POSTLOAD method (executed as separate query).
     */
    const BELONGS_TO = 103;

    /**
     * TODO: WRITE COMMENT
     */
    const MANY_TO_MANY = 104;

    /**
     * This is internal relation types, in most of cases relation like that will be created
     * automatically when relation detect that target is interface and not real class.
     */
    const BELONGS_TO_MORPHED = 108;
    const MANY_TO_MORPHED    = 109;

    /**
     * Constants used to declare relation schemas.
     */
    const OUTER_KEY         = 901; //Outer key name
    const INNER_KEY         = 902; //Inner key name
    const MORPH_KEY         = 903; //Morph key name
    const PIVOT_TABLE       = 904; //Pivot table name
    const PIVOT_COLUMNS     = 905; //Pre-defined pivot table columns
    const THOUGHT_INNER_KEY = 906; //Pivot table options
    const THOUGHT_OUTER_KEY = 907; //Pivot table options
    const WHERE             = 908; //Where conditions
    const WHERE_PIVOT       = 909; //Where pivot conditions

    /**
     * Additional constants used to control relation schema creation.
     */
    const INVERSE           = 1001; //Relation should be inverted to parent model
    const CONSTRAINT        = 1002; //Relation should create foreign keys (default)
    const CONSTRAINT_ACTION = 1003; //Default relation foreign key delete/update action (CASCADE)
    const CREATE_PIVOT      = 1004; //Many-to-Many should create pivot table automatically (default)
    const NULLABLE          = 1005; //Relation can be nullable (default)
    const CREATE_INDEXES  = 1006; //Indication that relation is allowed to create required indexes
    const MORPHED_ALIASES = 1007; //Aliases for morphed sub-relations

    /**
     * Constants used to declare index type. See documentation for indexes property.
     */
    const INDEX  = 1000;            //Default index type
    const UNIQUE = 2000;            //Unique index definition

    /**
     * Already fetched schemas from ORM. Yes, ORM ActiveRecord is really similar to ODM. Original ORM
     * was written long time ago before ODM and solutions i put to ORM was later used for ODM, while
     * "great transition" (tm) ODM was significantly updated and now ODM drive updates for ORM,
     * the student become the teacher.
     *
     * @var array
     */
    protected static $schemaCache = [];

    /**
     * ORM component.
     *
     * @var ORM
     */
    protected $orm = null;

    /**
     * Table associated with ActiveRecord. Spiral will guess table name automatically based on class
     * name use Doctrine Inflector, however i'm STRONGLY recommend to declare table name manually as
     * it gives more readable code.
     *
     * @var string
     */
    protected $table = null;

    /**
     * Database name/id where record table located in. By default database will be used if nothing
     * else is specified.
     *
     * @var string
     */
    protected $database = 'default';

    /**
     * Indication that model data was successfully fetched from database.
     *
     * @var bool
     */
    protected $loaded = false;

    /**
     * Populated when model loaded using many-to-many connection.
     *
     * @see getPivot();
     * @var array
     */
    protected $pivotData = [];

    /**
     * TODO: WRITE COMMENT
     *
     * @var array
     */
    protected $schema = [];

    /**
     * Set of indexes to be created for associated model table, indexes will be created only if
     * model has enabled ACTIVE_SCHEMA constant.
     *
     * Use constants INDEX and UNIQUE to describe indexes, you can also create compound indexes:
     * protected $indexes = [
     *      [self::UNIQUE, 'email'],
     *      [self::INDEX, 'board_id'],
     *      [self::INDEX, 'board_id', 'check_id']
     * ];
     *
     * @var array
     */
    protected $indexes = [];

    /**
     * Default values associated with record fields. This default values will be combined with values
     * fetched from table schema.
     *
     * @var array
     */
    protected $defaults = [];

    /**
     * ActiveRecord marked with solid state flag will be saved entirely without generating simplified
     * update operations with only changed fields.
     *
     * @var bool
     */
    protected $solidState = false;

    /**
     * List of updated fields associated with their original values.
     *
     * @var array
     */
    protected $updates = [];

    /**
     * Constructed and pre-cached set of relations.
     *
     * @var RelationInterface[]
     */
    protected $relations = [];

    public function __construct(array $data = [], $loaded = false, ORM $orm = null)
    {
        $this->orm = !empty($orm) ? $orm : ORM::getInstance();
        $this->loaded = $loaded;

        if (!isset(self::$schemaCache[$class = static::class]))
        {
            static::initialize();
            self::$schemaCache[$class] = $this->orm->getSchema($class);
        }

        //Prepared document schema
        $this->schema = self::$schemaCache[$class];

        if (isset($data[ORM::PIVOT_DATA]))
        {
            $this->pivotData = $data[ORM::PIVOT_DATA];
            unset($data[ORM::PIVOT_DATA]);
        }

        foreach ($this->schema[ORM::E_RELATIONS] as $relation => $definition)
        {
            if (array_key_exists($relation, $data))
            {
                $this->relations[$relation] = $data[$relation];
                unset($data[$relation]);
            }
        }

        //Merging with default values
        $this->fields = $data + $this->schema[ORM::E_COLUMNS];

        if (!$this->isLoaded())
        {
            //Non loaded models should be in solid state by default and require initial validation
            $this->solidState(true)->validationRequired = true;
        }
    }

    /**
     * Get model schema.
     *
     * @return array
     */
    public function ormSchema()
    {
        return $this->schema;
    }

    /**
     * Role name used in morphed relations to detect outer model table and class.
     *
     * @return string
     */
    public function getRoleName()
    {
        return $this->schema[ORM::E_ROLE_NAME];
    }

    public function setContext(array $context)
    {
        //TODO: implement later
    }

    /**
     * Change record solid state flag value. Record marked with solid state flag will be saved
     * entirely without generating simplified update operations with only changed fields.
     *
     * Attention, you have to carefully use forceUpdate flag with models without primary keys.
     *
     * @param bool $solidState  Solid state flag value.
     * @param bool $forceUpdate Mark all fields as changed to force update later.
     * @return static
     * @throws ORMException
     */
    public function solidState($solidState, $forceUpdate = false)
    {
        $this->solidState = $solidState;

        if ($forceUpdate)
        {
            if ($this->schema[ORM::E_PRIMARY_KEY])
            {
                $this->updates = $this->getCriteria();
            }
            else
            {
                $this->updates = $this->schema[ORM::E_COLUMNS];
            }
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
     * Is model were fetched from databases or recently created?
     *
     * @return bool
     */
    public function isLoaded()
    {
        return (bool)$this->loaded && !$this->isDeleted();
    }

    /**
     * Indication that model was deleted.
     *
     * @return bool
     */
    public function isDeleted()
    {
        return $this->loaded === self::DELETED;
    }

    /**
     * Get relation pivot data, only populated when model loaded under many-to-many relation.
     *
     * @return array
     */
    public function getPivot()
    {
        return $this->pivotData;
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
        return !in_array($field, $this->schema[ORM::E_SECURED])
        && !(
            $this->schema[ORM::E_FILLABLE]
            && !in_array($field, $this->schema[ORM::E_FILLABLE])
        );
    }

    /**
     * Get or create model relation by it's name and pre-loaded (optional) set of data.
     *
     * @param string $name
     * @param mixed  $data
     * @param bool   $loaded
     * @return RelationInterface
     */
    public function getRelation($name, $data = null, $loaded = false)
    {
        if (array_key_exists($name, $this->relations))
        {
            if (!is_object($this->relations[$name]))
            {
                $data = $this->relations[$name];
                unset($this->relations[$name]);

                //Loaded relation
                return $this->getRelation($name, $data, true);
            }

            return $this->relations[$name];
        }

        //Constructing relation
        if (!isset($this->schema[ORM::E_RELATIONS][$name]))
        {
            throw new ORMException("Undefined relation {$name} in model " . static::class . ".");
        }

        $relation = $this->schema[ORM::E_RELATIONS][$name];

        return $this->relations[$name] = $this->orm->relation(
            $relation[ORM::R_TYPE],
            $this,
            $relation[ORM::R_DEFINITION],
            $data,
            $loaded
        );
    }

    /**
     * Offset to retrieve.
     *
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset The offset to retrieve.
     * @return mixed
     */
    public function __get($offset)
    {
        if (isset($this->schema[ORM::E_RELATIONS][$offset]))
        {
            return $this->getRelation($offset)->getData();
        }

        return $this->getField($offset, true);
    }

    /**
     * Set value to one of field. Setter filter can be disabled by providing last argument.
     *
     * @param string $name   Field name.
     * @param mixed  $value  Value to set.
     * @param bool   $filter If false no filter will be applied (setter or accessor).
     */
    public function setField($name, $value, $filter = true)
    {
        if (!array_key_exists($name, $this->fields))
        {
            throw new ORMException("Undefined field '{$name}' in '" . static::class . "'.");
        }

        $original = $this->fields[$name];
        parent::setField($name, $value, $filter);

        if (!array_key_exists($name, $this->updates))
        {
            $this->updates[$name] = $original instanceof AccessorInterface
                ? $original->serializeData()
                : $original;
        }
    }

    /**
     * Offset to set.
     *
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset The offset to assign the value to.
     * @param mixed $value  The value to set.
     */
    public function __set($offset, $value)
    {
        if (isset($this->schema[ORM::E_RELATIONS][$offset]))
        {
            $this->getRelation($offset)->setData($value);

            return;
        }

        $this->setField($offset, $value, true);
    }

    /**
     * Direct access to relation by it's name.
     *
     * @param string $method
     * @param array  $arguments
     * @return RelationInterface
     */
    public function __call($method, array $arguments)
    {
        $relation = $this->getRelation($method);

        return empty($arguments) ? $relation : call_user_func_array($relation, $arguments);
    }

    /**
     * Get array of changed or created fields for specified ActiveRecord or accessor.
     *
     * @return array
     */
    protected function compileUpdates()
    {
        if (!$this->hasUpdates() && !$this->solidState)
        {
            return [];
        }

        $updates = [];
        foreach ($this->fields as $name => $field)
        {
            if ($field instanceof ORMAccessor && ($this->solidState || $field->hasUpdates()))
            {
                $updates[$name] = $field->compileUpdates($name);
                continue;
            }

            if (!$this->solidState && !array_key_exists($name, $this->updates))
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
        $this->updates = [];

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

        return $this->validator = Validator::make([
            'data'      => $this->fields,
            'validates' => $this->schema[ORM::E_VALIDATES]
        ]);
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
        $errors = [];
        foreach ($this->errors as $field => $error)
        {
            if (
                is_string($error)
                && substr($error, 0, 2) == Translator::I18N_PREFIX
                && substr($error, -2) == Translator::I18N_POSTFIX
            )
            {
                $error = $this->i18nMessage($error);
            }

            $errors[$field] = $error;
        }

        if ($reset)
        {
            $this->errors = [];
        }

        return $errors;
    }

    /**
     * Get instance of DBAL\Table associated with specified record.
     *
     * @param ORM      $orm      ORM component, will be received from container if not provided.
     * @param Database $database Database instance, will be received from container if not provided.
     * @return Table
     */
    public static function dbalTable(ORM $orm = null, Database $database = null)
    {
        /**
         * We can always get instance of ORM component from global scope.
         */
        $orm = !empty($orm) ? $orm : ORM::getInstance();
        $schema = $orm->getSchema(static::class);

        //We can bypass dbalDatabase() method here.
        $database = !empty($database) ? $database : $orm->getDatabase($schema[ORM::E_DB]);

        return $database->table($schema[ORM::E_TABLE]);
    }

    /**
     * Get associated orm Selector. Selectors used to build complex related queries and fetch
     * models from database.
     *
     * @param ORM $orm ORM component, will be received from container if not provided.
     * @return Selector
     */
    public static function ormSelector(ORM $orm = null)
    {
        return new Selector(static::class, !empty($orm) ? $orm : ORM::getInstance());
    }

    /**
     * Save record fields to associated table. Model has to be valid to be saved, in
     * other scenario method will return false, model errors can be found in getErrors() method.
     *
     * Events: saving, saved, updating, updated will be fired.
     *
     * @param bool $validate  Validate record fields before saving, enabled by default. Turning this
     *                        option off will increase performance but will make saving less secure.
     *                        You can use it when model data was not modified directly by user. By
     *                        default value is null which will force document to select behaviour
     *                        from FORCE_VALIDATION constant.
     * @param bool $relations Save all nested relations with valid foreign keys and etc, attention,
     *                        only pre-loaded or create relations will be saved for performance
     *                        reasons, no "MANY" relations will be saved. Enabled by default.
     * @return bool
     * @throws ORMException
     */
    public function save($validate = null, $relations = true)
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

            $lastID = static::dbalTable($this->orm)->insert(
                $this->fields = $this->serializeData()
            );

            if (!empty($primaryKey))
            {
                $this->fields[$primaryKey] = $lastID;
            }

            $this->loaded = true;
            $this->event('saved');
        }
        elseif ($this->solidState || $this->hasUpdates())
        {
            $this->event('updating');

            static::dbalTable($this->orm)->update(
                $this->compileUpdates(),
                $this->getCriteria()
            )->run();

            $this->event('updated');
        }

        $this->flushUpdates();

        if ($relations && !empty($this->relations))
        {
            //We would like to save all relations under one transaction, so we can easily revert them
            //all, in future it will be reasonable to save primary model and relations under one
            //transaction
            $this->orm->getDatabase($this->schema[ORM::E_DB])->transaction(function () use ($validate)
            {
                foreach ($this->relations as $name => $relation)
                {
                    if ($relation instanceof RelationInterface && !$relation->saveData($validate))
                    {
                        //Let's record error
                        $this->addError($name, $relation->getErrors());

                        throw new ORMException("Unable to save relation.");
                    }
                }
            });
        }

        return true;
    }

    /**
     * Delete record from database. Attention, if your model does not have primary key result of
     * this method can be pretty dramatic as it will remove every record from associated table with
     * same set of field.
     *
     * Events: deleting, deleted will be raised.
     */
    public function delete()
    {
        $this->event('deleting');

        if ($this->isLoaded())
        {
            static::dbalTable($this->orm)->delete($this->getCriteria())->run();
        }

        $this->fields = $this->schema[ORM::E_COLUMNS];
        $this->loaded = self::DELETED;

        $this->event('deleted');
    }

    /**
     * Get where condition to fetch current model from database, in cases where primary key is not
     * provided full model data will be used as where condition.
     *
     * @return array
     */
    protected function getCriteria()
    {
        if (!empty($this->schema[ORM::E_PRIMARY_KEY]))
        {
            return [$this->schema[ORM::E_PRIMARY_KEY] => $this->primaryKey()];
        }

        //We have to serialize model data
        return $this->updates + $this->serializeData();
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
    public static function create($fields = [])
    {
        /**
         * @var ActiveRecord $class
         */
        $class = new static();

        //Forcing validation (empty set of fields is not valid set of fields)
        $class->validationRequired = true;
        $class->setFields($fields)->event('created');

        return $class;
    }

    /**
     * Get ORM selector used to build complex SQL queries to fetch model and it's relations. Use
     * second argument to specify relations to be loaded.
     *
     * Example:
     * User::find(['status'=>'active'], ['profile']);
     *
     * @param array $where Selection WHERE statement.
     * @param array $load  Array or relations to be loaded.
     * @return Selector|static[]
     */
    public static function find(array $where = [], array $load = [])
    {
        return static::ormSelector()->load($load)->find($where);
    }

    /**
     * Alias for find method. Get ORM selector used to build complex SQL queries to fetch model and
     * it's relations. Use second argument to specify relations to be loaded.
     *
     * Example:
     * User::select(['status'=>'active'], ['profile']);
     *
     * @param array $where Selection WHERE statement.
     * @param array $load  Array or relations to be loaded.
     * @return Selector|static[]
     */
    public static function select(array $where = [], array $load = [])
    {
        return static::find($where, $load);
    }

    /**
     * Fetch one record from database or return null. Use second argument to specify relations to be
     * loaded.
     *
     * Example:
     * User::findOne(['name'=>'Wolfy-J'], ['profile'], ['id'=>'DESC']);
     *
     * @param array $where   Selection WHERE statement.
     * @param array $load    Array or relations to be loaded. You can't use INLOAD or JOIN_ONLY methods
     *                       with findOne.
     * @param array $orderBy Sort by conditions.
     * @return static|null
     */
    public static function findOne(array $where = [], array $load = [], array $orderBy = [])
    {
        $selector = static::find($where, $load);

        foreach ($orderBy as $column => $direction)
        {
            $selector->orderBy($column, $direction);
        }

        return $selector->findOne();
    }

    /**
     * Fetch one record from database by primary key value.
     *
     * Example:
     * User::findByID(1, ['profile']);
     *
     * @param mixed $id      Primary key.
     * @param array $load    Array or relations to be loaded. You can't use INLOAD or JOIN_ONLY methods
     *                       with findOne.
     * @return static|null
     */
    public static function findByPK($id = null, array $load = [])
    {
        return static::ormSelector()->load($load)->findByPK($id);
    }

    /**
     * Simplified way to dump information.
     *
     * @return Object
     */
    public function __debugInfo()
    {
        $info = [
            'table'     => $this->schema[ORM::E_DB] . '/' . $this->schema[ORM::E_TABLE],
            'pivotData' => $this->pivotData,
            'fields'    => $this->getFields(),
            'errors'    => $this->getErrors()
        ];

        if (empty($this->pivotData))
        {
            unset($info['pivotData']);
        }

        return (object)$info;
    }

    /**
     * Clear existed schema cache.
     */
    public static function clearSchemaCache()
    {
        self::$schemaCache = [];
    }
}