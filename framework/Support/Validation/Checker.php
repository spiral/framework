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

abstract class Checker
{
    /**
     * Localization and indexation support.
     */
    use LocalizableTrait;

    /**
     * We are going to inherit parent validation, we have to let i18n indexer know to collect both
     * local and parent messages under one bundle.
     */
    const I18N_INHERIT_MESSAGES = true;

    /**
     * Set of default error messages associated with their check methods organized by method name.
     * Will be returned by the checker to replace the default validator message. Can have placeholders
     * for interpolation.
     *
     * @var array
     */
    protected $messages = array();

    /**
     * Validator instance, can be used for complex and composite validations.
     *
     * @var Validator
     */
    protected $validator = null;

    /**
     * Perform value check using specified checker method, value and arguments. Validator instance
     * can be provided to create appropriate context for complex and composite validations.
     *
     * @param string    $method    Checker method name.
     * @param mixed     $value     Value to be validated.
     * @param array     $arguments Additional arguments will be provided to checker method AFTER value.
     * @param Validator $validator Validator instance initiated validation session.
     * @return mixed
     */
    public function check($method, $value, array $arguments = array(), Validator $validator = null)
    {
        array_unshift($arguments, $value);

        $this->validator = $validator;
        $result = call_user_func_array(array($this, $method), $arguments);
        $this->validator = null;

        return $result;
    }

    /**
     * Return custom error message associated with checker methods. Return empty string if no methods
     * associated.
     *
     * @param string           $method     Checker method name.
     * @param \ReflectionClass $reflection Source to fetch messages from.
     * @return string
     */
    public function getMessage($method, \ReflectionClass $reflection = null)
    {
        if (!empty($reflection))
        {
            $messages = $reflection->getDefaultProperties()['messages'];
            if (isset($messages[$method]))
            {
                //We are inheriting parent messages
                return $this->i18nMessage($messages[$method]);
            }
        }
        elseif (isset($this->messages[$method]))
        {
            return $this->i18nMessage($this->messages[$method]);
        }

        //Looking for message in parent realization
        $reflection = $reflection ?: new \ReflectionClass($this);
        if ($reflection->getParentClass())
        {
            return $this->getMessage($method, $reflection->getParentClass());
        }

        return '';
    }

    /**
     * Models and other classes which uses LocalizableTrait interface allowed to be automatically
     * parsed and analyzed for messages stored in default property values (static and non static),
     * such values can be prepended and appended with i18n prefixes ([[ and ]] by default) and will
     * be localized on output. Class should implement i18nNamespace method (static) which will define
     * required i18n namespace. Namespace will be used to translate default messages and by i18n
     * component to index this messages to localization bundles.
     *
     * Class should will be responsible by itself to localize such messages. Use "@do-not-index" doc
     * comment to prevent field from indexation.
     *
     * Additionally method i18nMessage() introduced, this method can be used to localize default
     * model messages from attributes ([[ and ]] will be cut) or be called directly in one of model
     * method. Both usages will be indexed and captured to bundles.
     *
     * @return string
     */
    final public static function i18nBundle()
    {
        return get_called_class();
    }
}