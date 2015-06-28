<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Support\Validation;

use Spiral\Components\I18n\LocalizableTrait;
use Spiral\Core\Component;
use Spiral\Core\Container;

class Validator extends Component
{
    /**
     * Localization and indexation support.
     */
    use LocalizableTrait, Component\LoggerTrait;

    /**
     * Errors added manually to validator using addError() method will get this condition type.
     */
    const FORCED_ERROR = "forced";

    /**
     * Rules declared in empty conditions should return this value to let system know that future
     * field validation can be skipped.
     */
    const STOP_VALIDATION = -99;

    /**
     * Container.
     *
     * @invisible
     * @var Container
     */
    protected $container = null;

    /**
     * Rules with that condition should be treated as not empty stop flag, this means, that all
     * other conditions attached to same field name will be skipped if not empty rule will fail. Use
     * it to define required and non required fields with optional conditions to perform only when
     * field has value.
     *
     * @var array
     */
    protected $emptyConditions = [
        "notEmpty",
        "required",
        "type::notEmpty",
        "required::with",
        "required::without",
        "required::withAll",
        "required::withoutAll"
    ];

    /**
     * Default message to apply as error when rule validation failed, has lowest priority and will
     * be replaced by custom checker or user defined message. Can be automatically interpolated
     * with condition and field names.
     *
     * @var string
     */
    protected $defaultMessage = "Condition '{condition}' does not meet for field '{field}'.";

    /**
     * Validator can automatically interpolate all output messages with field name if this property
     * is set to true, however this is not optimal if you want to pass messages to models for future
     * internalization.
     *
     * @var bool
     */
    protected $interpolateNames = true;

    /**
     * List of custom validator checkers. Checker can be registered using registerChecker() method,
     * or received via getChecker().
     *
     * Every checker can provide set of validation methods (conditions), which can be called by using
     * expression "checker::condition" where checker is alias class or object binded to. As any other
     * function used to check field, checker conditions can accept additional arguments collected
     * from rule. Checker classes resolved using IoC container and can depend on other tools.
     * Additionally checker will receive validator instance, so they can be used for complex and
     * composite data checks (use validator->getField()).
     *
     * @var array
     */
    protected static $checkers = [
        "type"     => 'Spiral\Support\Validation\Checkers\TypeChecker',
        "required" => 'Spiral\Support\Validation\Checkers\RequiredChecker',
        "number"   => 'Spiral\Support\Validation\Checkers\NumberChecker',
        "mixed"    => 'Spiral\Support\Validation\Checkers\MixedChecker',
        "address"  => 'Spiral\Support\Validation\Checkers\AddressChecker',
        "string"   => 'Spiral\Support\Validation\Checkers\StringChecker',
        "file"     => 'Spiral\Support\Validation\Checkers\FileChecker',
        "image"    => 'Spiral\Support\Validation\Checkers\ImageChecker'
    ];

    /**
     * Short aliases between validation condition and checker method or external class, or external
     * function. Used to simplify development.
     *
     * @var array
     */
    protected static $aliases = [
        "notEmpty"   => "type::notEmpty",
        "required"   => "type::notEmpty",

        "datetime"   => "type::datetime",
        "timezone"   => "type::timezone",
        "bool"       => "type::boolean",
        "boolean"    => "type::boolean",
        "cardNumber" => "mixed::cardNumber",
        "regexp"     => "string::regexp",
        "email"      => "address::email",
        "url"        => "address::url",
        "file"       => "file::exists",
        "uploaded"   => "file::uploaded",
        "filesize"   => "file::size",
        "image"      => "image::valid",
        "array"      => "is_array",
        "callable"   => "is_callable",
        "double"     => "is_double",
        "float"      => "is_float",
        "int"        => "is_int",
        "integer"    => "is_integer",
        "long"       => "is_long",
        "null"       => "is_null",
        "object"     => "is_object",
        "real"       => "is_real",
        "resource"   => "is_resource",
        "scalar"     => "is_scalar",
        "string"     => "is_string"
    ];

    /**
     * Flag if validation was already applied for provided fields.
     *
     * @var bool
     */
    protected $validated = false;

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
     * "status" => array(
     *      ["notEmpty"],
     *      ["string::shorter", 10, "error" => "Your string is too short."],
     *      [["MyClass","myMethod"], "error" => "Custom validation failed."]
     * ),
     * "email" => array(
     *      ["notEmpty", "error" => "Please enter your email address."],
     *      ["email", "error" => "Email is not valid."]
     * ),
     * "pin" => array(
     *      ["string::regexp", "/[0-9]{5}/", "error" => "Invalid pin format, if you don't know your
     *                                                   pin, please skip this field."]
     * ),
     * "flag" => array(
     *      ["notEmpty"], ["boolean"]
     * )
     *
     * P.S. "$validates" is common name for validation rules property in validator and modes.
     *
     * @var array
     */
    protected $validates = [];

    /**
     * Data to be validated. Nothing else to say.
     *
     * @var array|\ArrayAccess
     */
    protected $data = [];

    /**
     * Error messages collected during validating input data, by default one field associated with
     * first fail message, this behaviour can be changed by rewriting validator.
     *
     * @var array
     */
    protected $errors = [];

    /**
     * If true (set by default), validator will stop checking field rules after first fail. This is
     * default behaviour used to render model errors and spiral frontend.
     *
     * @var bool
     */
    protected $singlePass = true;

    /**
     * Validator instance with specified input data and validation rules.
     *
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
     * "status" => array(
     *      ["notEmpty"],
     *      ["string::shorter", 10, "error" => "Your string is too short."],
     *      [["MyClass","myMethod"], "error" => "Custom validation failed."]
     * ),
     * "email" => array(
     *      ["notEmpty", "error" => "Please enter your email address."],
     *      ["email", "error" => "Email is not valid."]
     * ),
     * "pin" => array(
     *      ["string::regexp", "/[0-9]{5}/", "error" => "Invalid pin format, if you don't know your
     *                                                   pin, please skip this field."]
     * ),
     * "flag" => array(
     *      ["notEmpty"], ["boolean"]
     * )
     *
     * @param array|\ArrayAccess $data             Data to be validated.
     * @param array              $validates        Validation rules.
     * @param bool               $interpolateNames If true all messages will be interpolated with field
     *                                             name included, in other scenario only method parameters
     *                                             will be embedded, this option can be disabled by
     *                                             model or outside to mount custom field labels.
     * @param Container          $container        Container instance used to resolve checkers, global
     *                                             container will be used if nothing else provided.
     */
    public function __construct(
        $data,
        array $validates,
        $interpolateNames = true,
        Container $container = null
    )
    {
        $this->data = $data;
        $this->validates = $validates;
        $this->interpolateNames = $interpolateNames;

        $this->container = $container ?: Container::getInstance();
    }

    /**
     * Update validation data (context), this method will automatically clean all existed error
     * messages and set validated flag to false.
     *
     * @param array|\ArrayAccess $data Data to be validated.
     * @return Validator
     */
    public function setData($data)
    {
        $this->validated = false;

        $this->data = $data;
        $this->errors = [];

        return $this;
    }

    /**
     * Retrieve field value from data array. Can be used in validator Checker classes as they are
     * receiving validator instance during condition check.
     *
     * @param string $field   Data field to retrieve.
     * @param mixed  $default Default value to return.
     * @return null
     */
    public function getField($field, $default = null)
    {
        $value = isset($this->data[$field]) ? $this->data[$field] : $default;

        return $value instanceof ValueInterface ? $value->serializeData() : $value;
    }

    /**
     * Registering short alias between validation condition and checker method or external class, or
     * external function. Used to simplify development.
     *
     * @param string $name     Alias name.
     * @param mixed  $callback Callback or closure or string.
     */
    public static function setValidationAlias($name, $callback)
    {
        static::$aliases[$name] = $callback;
    }

    /**
     * Register new checker instance or class (or replace existed one), Checker can extend validation
     * functionality by defining their own methods and default error messages. Every checker will
     * receive validator instance during processing, which allows to create complex and composite
     * validations.
     *
     * Every checker can provide set of validation methods (conditions), which can be called by using
     * expression "checker::condition" where checker is alias class or object binded to. As any other
     * function used to check field, checker conditions can accept additional arguments collected
     * from rule. Checker classes resolved using IoC container and can depend on other tools.
     * Additionally checker will receive validator instance, so they can be used for complex and
     * composite data checks (use validator->getField()).
     *
     * @param string $name    Checker alias to be used as prefix for all checker methods.
     * @param mixed  $checker Checker instance or class name (to create on demand).
     */
    static public function registerChecker($name, $checker)
    {
        static::$checkers[$name] = $checker;
    }

    /**
     * Receive checker instance previously registered by registerChecker() or defined in default
     * spiral checkers set.
     *
     * Every checker can provide set of validation methods (conditions), which can be called by using
     * expression "checker::condition" where checker is alias class or object binded to. As any other
     * function used to check field, checker conditions can accept additional arguments collected
     * from rule. Checker classes resolved using IoC container and can depend on other tools.
     * Additionally checker will receive validator instance, so they can be used for complex and
     * composite data checks (use validator->getField()).
     *
     * @param string $name Checker name.
     * @return Checker
     * @throws ValidationException
     */
    public function getChecker($name)
    {
        if (!isset(self::$checkers[$name]))
        {
            throw new ValidationException(
                "Unable to create validation checker defined by '{$name}' name."
            );
        }

        if (is_object(self::$checkers[$name]))
        {
            return self::$checkers[$name];
        }

        return self::$checkers[$name] = $this->container->get(self::$checkers[$name]);
    }

    /**
     * Helper methods, apply validation rules to existed data fields and collect validation error
     * messages. Can be redefined by custom behaviour.
     */
    protected function validate()
    {
        $this->errors = [];

        foreach ($this->validates as $field => $rules)
        {
            foreach ($rules as $rule)
            {
                if (isset($this->errors[$field]) && $this->singlePass)
                {
                    continue;
                }

                $condition = $rule[0];
                if (empty($this->data[$field]) && !in_array($condition, $this->emptyConditions))
                {
                    //There is no need to validate empty field except for special conditions
                    break;
                }

                $result = $this->check(
                    $field,
                    $condition,
                    $this->getField($field),
                    $arguments = $this->fetchArguments($rule)
                );

                if ($result instanceof Checker)
                {
                    //Custom message handling
                    if ($message = $result->getMessage($condition[1]))
                    {
                        $this->addMessage(
                            $field,
                            $this->fetchMessage($rule, $message),
                            $condition,
                            $arguments
                        );

                        continue;
                    }

                    $result = false;
                }

                if ((bool)$result)
                {
                    //Success
                    continue;
                }

                if ($result == self::STOP_VALIDATION)
                {
                    break;
                }

                //Recording error message
                $this->addMessage(
                    $field,
                    $this->fetchMessage($rule, $this->i18nMessage($this->defaultMessage)),
                    $rule[0],
                    $arguments
                );
            }
        }
    }

    /**
     * Helper method to apply validation condition to field value, will automatically detect
     * condition type (function name, callback or checker condition).
     *
     * @param string $field     Field name.
     * @param mixed  $condition Condition definition (see rules).
     * @param mixed  $value     Value to be checked.
     * @param array  $arguments Additional arguments will be provided to check function or method
     *                          AFTER value.
     * @return bool
     * @throws ValidationException
     */
    protected function check($field, &$condition, $value, array $arguments)
    {
        if (is_string($condition) && isset(self::$aliases[$condition]))
        {
            $condition = self::$aliases[$condition];
        }

        try
        {
            //Aliased condition
            if (strpos($condition, '::'))
            {
                $condition = explode('::', $condition);
                if (isset(self::$checkers[$condition[0]]))
                {
                    $checker = $this->getChecker($condition[0]);
                    if (!$result = $checker->check($condition[1], $value, $arguments, $this))
                    {
                        //To let validation() method know that message should be handled via Checker
                        return $checker;
                    }

                    return $result;
                }
            }

            if (is_string($condition) || is_array($condition))
            {
                array_unshift($arguments, $value);

                return call_user_func_array($condition, $arguments);
            }
        }
        catch (\ErrorException $exception)
        {
            $condition = func_get_arg(1);
            if (is_array($condition))
            {
                if (is_object($condition[0]))
                {
                    $condition[0] = get_class($condition[0]);
                }

                $condition = join('::', $condition);
            }

            self::logger()->error(
                "Condition '{condition}' failed with '{exception}' while checking '{field}' field.",
                compact('condition', 'field') + ['exception' => $exception->getMessage()]
            );

            return false;
        }

        return true;
    }

    /**
     * Fetch additional validation arguments from rule. See rules explanation for more information.
     *
     * @param array $rule Rule definition.
     * @return array
     */
    protected function fetchArguments(array $rule)
    {
        unset($rule[0], $rule['message'], $rule['error']);

        return array_values($rule);
    }

    /**
     * Fetch message from validation rule or use default message defined by validator or checker
     * instances.
     *
     * @param array  $rule    Rule definition.
     * @param string $message Default message to use.
     * @return mixed
     */
    protected function fetchMessage(array $rule, $message)
    {
        $message = isset($rule['message']) ? $rule['message'] : $message;
        $message = isset($rule['error']) ? $rule['error'] : $message;

        return $message;
    }

    /**
     * Helper method used to register error message to error array. If interpolateMessages property
     * set to true message will be automatically interpolated with field and condition names.
     *
     * @param string $field     Field name.
     * @param string $message   Error message to be added.
     * @param mixed  $condition Condition definition (will be converted to string to interpolate).
     * @param array  $arguments Additional condition arguments.
     */
    protected function addMessage($field, $message, $condition, array $arguments = [])
    {
        if (is_array($condition))
        {
            if (is_object($condition[0]))
            {
                $condition[0] = get_class($condition[0]);
            }

            $condition = join('::', $condition);
        }

        if ($this->interpolateNames)
        {
            $this->errors[$field] = interpolate($message, compact('field', 'condition') + $arguments);
        }
        else
        {
            $this->errors[$field] = interpolate($message, compact('condition') + $arguments);
        }
    }

    /**
     * Validate data (if not already validated) and return validation status, true if all fields
     * passed validation and false is some error messages collected (error messages can be forced
     * manually using addError() method).
     *
     * @return bool
     */
    public function isValid()
    {
        !$this->validated && $this->validate();

        return !(bool)$this->errors;
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
     * Manually force error for some field ("forced" condition will be used).
     *
     * @param string $field
     * @param string $message Custom error message, will be interpolated if interpolateMessages
     *                        property set to true.
     */
    public function addError($field, $message)
    {
        $this->addMessage($field, $message, static::FORCED_ERROR, []);
    }

    /**
     * Validate data (if not already) and return all error messages associated with their field names.
     * Output format can vary based on validator implementation.
     *
     * @return array
     */
    public function getErrors()
    {
        !$this->validated && $this->validate();

        return $this->errors;
    }

    /**
     * Creates validator with specified input data and validation rules, use return argument to
     * return validator itself or only validation status.
     *
     * Validation rules explanation:
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
     * "status" => array(
     *      ["notEmpty"],
     *      ["string::shorter", 10, "error" => "Your string is too short."],
     *      [["MyClass","myMethod"], "error" => "Custom validation failed."]
     * ),
     * "email" => array(
     *      ["notEmpty", "error" => "Please enter your email address."],
     *      ["email", "error" => "Email is not valid."]
     * ),
     * "pin" => array(
     *      ["string::regexp", "/[0-9]{5}/", "error" => "Invalid pin format, if you don't know your
     *                                                   pin, please skip this field."]
     * ),
     * "flag" => array(
     *      ["notEmpty"], ["boolean"]
     * )
     *
     * @param array|\ArrayAccess $data      Data to be validated.
     * @param array              $validates Validation rules.
     * @param Container          $container Container instance to use to resolve checkers.
     * @return bool|Validator
     */
    public static function create($data, array $validates, Container $container = null)
    {
        return static::make(compact('data', 'validates'), $container);
    }
}