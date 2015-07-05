<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Support\Models;

use Spiral\Components\I18n\Translator;
use Spiral\Components\I18n\LocalizableTrait;
use Spiral\Core\Component;
use Spiral\Support\Models\Schemas\DataEntitySchema;
use Spiral\Support\Validation\Validator;

abstract class DataEntity extends Component implements \JsonSerializable, \IteratorAggregate, \ArrayAccess
{
    /**
     * Model events and localization.
     */
    use LocalizableTrait, Component\LoggerTrait, Component\EventsTrait;

    /**
     * Such option will be passed to trait initializers when some component requested model schema
     * analysis.
     */
    const SCHEMA_ANALYSIS = 788;

    /**
     * Mutator aliases. Aliases used to simplify definition of DataEntity setters and getters. New
     * filter alias can be defined at any moment by application or module. Aliases can also be used
     * for accessors.
     *
     * @var array
     */
    public static $mutatorAliases = [
        'escape'      => ['Spiral\Helpers\StringHelper', 'escape'],
        'string'      => ['Spiral\Helpers\ValueHelper', 'castString'],
        'boolean'     => ['Spiral\Helpers\ValueHelper', 'castBoolean'],
        'scalarArray' => ['Spiral\Helpers\ValueHelper', 'scalarArray'],
        'timestamp'   => 'Spiral\Support\Models\Accessors\Timestamp'
    ];

    /**
     * Indication that model was already initiated.
     *
     * @var array
     */
    protected static $initiatedModels = [];

    /**
     * Cache of error messages ordered by their definition parent.
     *
     * @var array
     */
    protected static $messagesCache = [];

    /**
     * Fields to apply filters and validations, this is primary model data, which can be set using
     * setFields() method and retrieved using getFields() or publicFields().
     *
     * @var array
     */
    protected $fields = [];

    /**
     * List of secured fields, such fields can not be set using setFields() method (only directly).
     *
     * @var array
     */
    protected $secured = [];

    /**
     * Set of fields which can be assigned using setFields() method, if property is empty every field
     * except secured will be assignable. Fields can still be assigned directly using setField() or
     * __set() methods without any limitations.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * List of hidden fields can not be fetched using publicFields() method (only directly).
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * Validator instance will be used to check model fields.
     *
     * @var Validator
     */
    protected $validator = null;

    /**
     * Indication that validation is required, flag can be set when some field changed or in other
     * conditions when data has to be revalidated.
     *
     * @var bool
     */
    protected $validationRequired = false;

    /**
     * Set of validation rules associated with their field. Every field can have one or multiple
     * rules assigned, however after first fail system will stop checking that field. This used to
     * prevent cascade validation failing. You can redefine property singleError and addMessage
     * function to specify different behaviour.
     *
     * Every rule should include condition (callback, function name or checker condition).
     * Additionally spiral validator supports custom validation messages which can be associated
     * with one condition by defining key "message" or "error", and additional argument which will
     * be passed to validation function AFTER field value.
     *
     * Default message provided by validator OR by checker (has higher priority that validation
     * message) will be used if you did not specify any custom rule.
     *
     * Validator will skip all empty or not defined values, to force it's validation use specially
     * designed rules like "notEmpty", "required", "requiredWith" and etc.
     *
     * Examples:
     * "status" => [
     *      ["notEmpty"],
     *      ["string::shorter", 10, "error" => "Your string is too short."],
     *      [["MyClass","myMethod"], "error" => "Custom validation failed."]
     * [,
     * "email" => [
     *      ["notEmpty", "error" => "Please enter your email address."],
     *      ["email", "error" => "Email is not valid."]
     * [,
     * "pin" => [
     *      ["string::regexp", "/[0-9]{5}/", "error" => "Invalid pin format, if you don't know your
     *                                                   pin, please skip this field."]
     * [,
     * "flag" => ["notEmpty", "boolean"]
     *
     * In cases where you don't need custom message or check parameters you can use simplified
     * rule syntax:
     *
     * "flag" => ["notEmpty", "boolean"]
     *
     * P.S. "$validates" is common name for validation rules property in validator and modes.
     *
     * @var array
     */
    protected $validates = [];

    /**
     * Validation and model errors.
     *
     * @var array
     */
    protected $errors = [];

    /**
     * Field getters, will be executed when field are received.
     *
     * @var array
     */
    protected $getters = [];

    /**
     * Field setters, called when field assigned by setField() or setFields().
     *
     * @var array
     */
    protected $setters = [];

    /**
     * Accessors. By using accessor some model value can be mocked up with class "representative"
     * like DateTime for timestamp field. Accessors will be used only on direct field access and will
     * be "serialized" in getFields(), publicFields() methods. Do not use accessors in combination
     * with setters/getters, this is standalone way to manipulate value.
     *
     * @var array
     */
    protected $accessors = [];

    /**
     * Register new filter alias. Filter aliases used by getters and setters.
     *
     * @param string   $alias  Alias name.
     * @param callable $filter Filter to be applied, should be valid callable.
     */
    public static function setMutatorAlias($alias, $filter)
    {
        static::$mutatorAliases[$alias] = $filter;
    }

    /**
     * Get mutator for specified field. Setters, getters and accessors can be retrieved using this
     * method.
     *
     * @param string $field   Field name.
     * @param string $mutator Mutator type (setter, getter, accessor).
     * @return mixed|null
     */
    protected function getMutator($field, $mutator)
    {
        //We do support 3 mutators: getter, setter and accessor, all of them can be
        //referenced to valid field name by adding "s" at the end
        $mutator = $mutator . 's';

        if (isset($this->{$mutator}[$field]))
        {
            $filter = $this->{$mutator}[$field];

            if (is_string($filter) && isset(self::$mutatorAliases[$filter]))
            {
                return self::$mutatorAliases[$filter];
            }

            return $filter;
        }

        return null;
    }

    /**
     * Get accessor instance.
     *
     * @param mixed  $value    Value to mock up.
     * @param string $accessor Accessor definition (can be array).
     * @return AccessorInterface
     */
    protected function defineAccessor($value, $accessor)
    {
        $options = null;
        if (is_array($accessor))
        {
            list($accessor, $options) = $accessor;
        }

        return new $accessor($value, $this, $options);
    }

    /**
     * Get one specific field value and apply getter filter to it. You can disable getter filter by
     * providing second argument.
     *
     * @param string $name    Field name.
     * @param bool   $filter  If false no filter will be applied.
     * @param mixed  $default Default value to return if field not set.
     * @return mixed|AccessorInterface
     */
    public function getField($name, $filter = true, $default = null)
    {
        $value = isset($this->fields[$name]) ? $this->fields[$name] : $default;

        if ($value instanceof AccessorInterface)
        {
            return $value;
        }

        if ($accessor = $this->getMutator($name, 'accessor'))
        {
            return $this->fields[$name] = $this->defineAccessor($value, $accessor);
        }

        if ($filter && $filter = $this->getMutator($name, 'getter'))
        {
            try
            {
                return call_user_func($filter, $value);
            }
            catch (\ErrorException $exception)
            {
                self::logger()->warning(
                    "Failed to apply filter to '{name}' field.", compact('name')
                );

                return null;
            }
        }

        return $value;
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
        if ($value instanceof AccessorInterface)
        {
            $this->fields[$name] = $value->embed($this);

            return;
        }

        if ($filter && $accessor = $this->getMutator($name, 'accessor'))
        {
            if (!isset($this->fields[$name]))
            {
                $this->fields[$name] = null;
            }

            if (!($this->fields[$name] instanceof AccessorInterface))
            {
                $this->fields[$name] = $this->defineAccessor($this->fields[$name], $accessor);
            }

            $this->fields[$name]->setData($value);

            return;
        }

        if ($filter && $filter = $this->getMutator($name, 'setter'))
        {
            try
            {
                $value = call_user_func($filter, $value);
            }
            catch (\ErrorException $exception)
            {
                $value = call_user_func($filter, null);
                self::logger()->warning("Failed to apply filter to '{name}' field.", compact('name'));
            }
        }

        $this->fields[$name] = $value;
        $this->validationRequired = true;
    }

    /**
     * Whether a offset exists.
     *
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset An offset to check for.
     * @return bool
     */
    public function __isset($offset)
    {
        return array_key_exists($offset, $this->fields);
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
        return $this->getField($offset, true);
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
        $this->setField($offset, $value);
    }

    /**
     * Offset to unset.
     *
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset The offset to unset.
     */
    public function __unset($offset)
    {
        unset($this->fields[$offset]);
        $this->validationRequired = true;
    }

    /**
     * Whether a offset exists.
     *
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset An offset to check for.
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return $this->__isset($offset);
    }

    /**
     * Offset to retrieve.
     *
     * @link   http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset The offset to retrieve.
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->getField($offset);
    }

    /**
     * Offset to set.
     *
     * @link   http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset The offset to assign the value to.
     * @param mixed $value  The value to set.
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->setField($offset, $value);
    }

    /**
     * Offset to unset.
     *
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset The offset to unset.
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->__unset($offset);
    }

    /**
     * Serialize object data for saving into database. No getters will be applied here.
     *
     * @return mixed
     */
    public function serializeData()
    {
        $result = $this->fields;
        foreach ($result as $field => $value)
        {
            if ($value instanceof AccessorInterface)
            {
                $result[$field] = $value->serializeData();
            }
        }

        return $result;
    }

    /**
     * Get all models fields. All accessors will be automatically converted to their values.
     *
     * @param bool $filter Apply getters.
     * @return array
     */
    public function getFields($filter = true)
    {
        $result = [];
        foreach ($this->fields as $name => &$field)
        {
            $result[$name] = $this->getField($name, $filter);
        }

        return $result;
    }

    /**
     * Check if field assignable.
     *
     * @param string $field
     * @return bool
     */
    protected function isFillable($field)
    {
        return !in_array($field, $this->secured) && !(
            !empty($this->fillable) && !in_array($field, $this->fillable)
        );
    }

    /**
     * Update multiple non-secured model fields. Event "setFields" raised here.
     *
     * @param array|\Traversable $fields
     * @return static
     */
    public function setFields($fields = [])
    {
        if (!is_array($fields) && !$fields instanceof \Traversable)
        {
            return $this;
        }

        foreach ($this->event('setFields', $fields) as $name => $field)
        {
            $this->isFillable($field) && $this->setField($name, $field, true);
        }

        return $this;
    }

    /**
     * Get all non secured model fields. Additional processing can be applied to fields here.
     *
     * @return array
     */
    public function publicFields()
    {
        $fields = $this->getFields();
        foreach ($this->hidden as $secured)
        {
            unset($fields[$secured]);
        }

        return $this->event('publicFields', $fields);
    }

    /**
     * Retrieve an external iterator. An instance of an object implementing Iterator or Traversable.
     *
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return \Traversable
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->getFields());
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
            'validates' => $this->validates
        ]);
    }

    /**
     * Request validation.
     *
     * @return static
     */
    public function requestValidation()
    {
        $this->validationRequired = true;

        return $this;
    }

    /**
     * Validating model data using validation rules, all errors will be stored in model errors array.
     * Errors will not be erased between function calls.
     *
     * @return bool
     */
    protected function validate()
    {
        if (empty($this->validates))
        {
            $this->validationRequired = false;
        }
        elseif ($this->validationRequired)
        {
            $this->event('validation');

            $this->errors = $this->getValidator()->getErrors();
            $this->validationRequired = false;

            //Cleaning memory
            $this->validator->setData([]);
            $this->errors = $this->event('validated', $this->errors);
        }

        return empty($this->errors);
    }

    /**
     * Validate data and return validation status, true if all fields passed validation and false is
     * some error messages collected (error messages can be forced manually using addError() method).
     *
     * @return bool
     */
    public function isValid()
    {
        $this->validate();

        return !((bool)$this->errors);
    }

    /**
     * Evil tween of isValid() method: validate data (if not already validated) and return true if
     * any validation error occurred including errors added using addError() method.
     *
     * @return bool
     */
    public function hasErrors()
    {
        return !$this->isValid();
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
     * Adding error message. You can use this function to assign error manually. This message will be
     * localized same way as other messages, however system will not be able to index them.
     *
     * To use custom errors combined with location use ->addError($model->getMessage()) and store your
     * custom error messages in model::$messages array.
     *
     * @param string       $field   Field storing message.
     * @param string|array $message Message to be added.
     */
    public function addError($field, $message)
    {
        $this->errors[$field] = $message;
    }

    /**
     * Initialize model by calling it's methods named using pattern __init*. Such methods can be
     * protected and will be called only once, on first model constructing.
     *
     * @param mixed $options Custom options passed to initializer. Providing option will force
     *                       initialization methods even if entity already initiated.
     */
    protected static function initialize($options = null)
    {
        if (isset(self::$initiatedModels[$class = get_called_class()]) && empty($options))
        {
            return;
        }

        foreach (get_class_methods($class) as $method)
        {
            if (substr($method, 0, 4) === 'init' && $method != 'initialize')
            {
                forward_static_call(['static', $method], $options);
            }
        }

        self::$initiatedModels[$class] = true;
    }

    /**
     * Prepare document property before caching it ORM schema. This method fire event "property" and
     * sends SCHEMA_ANALYSIS option to trait initializers. Method can be used to create custom filters,
     * schema values and etc.
     *
     * @param DataEntitySchema $schema
     * @param string           $property Model property name.
     * @param mixed            $value    Model property value, will be provided in an inherited form.
     * @return mixed
     */
    public static function describeProperty(DataEntitySchema $schema, $property, $value)
    {
        static::initialize(self::SCHEMA_ANALYSIS);

        return static::dispatcher()->fire('describe', compact('schema', 'property', 'value'))['value'];
    }

    /**
     * Destructing model fields, filters and validator.
     */
    public function __destruct()
    {
        $this->fields = [];
        $this->validator = null;
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
        return $this->event('jsonSerialize', $this->publicFields());
    }
}