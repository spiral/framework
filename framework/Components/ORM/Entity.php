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
use Spiral\Components\ORM\Schemas\EntitySchema;
use Spiral\Core\Events\EventDispatcher;
use Spiral\Support\Models\DataEntity;
use Spiral\Support\Validation\Validator;

class Entity extends DataEntity
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
     * TODO EXAMPLES!!!!
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

    protected $schema = array();
    protected $indexes = array();

    protected $defaults = array();

    public function __construct($fields = array())
    {
        if (!isset(self::$schemaCache[$class = get_class($this)]))
        {
            static::initialize();
            self::$schemaCache[$class] = ORM::getInstance()->getSchema(get_class($this));
        }

        //Prepared document schema
        $this->schema = self::$schemaCache[$class];

        //Merging with default values
        $this->fields = $fields + $this->schema[ORM::E_COLUMNS];
    }

    /**
     * Get document primary key (_id) value. This value can be used to identify if model loaded from
     * databases or just created.
     *
     * @return \MongoId
     */
    public function primaryKey()
    {
        return isset($this->fields[$this->schema[ORM::E_PRIMARY_KEY]])
            ? $this->fields[ORM::E_PRIMARY_KEY]
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
}