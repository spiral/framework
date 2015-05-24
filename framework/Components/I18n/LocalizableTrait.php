<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Components\I18n;

trait LocalizableTrait
{
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
     * If you want to index both local and parent messages, set constant I18N_INHERIT_MESSAGES to true.
     *
     * @return string
     */
    public static function i18nBundle()
    {
        return get_called_class();
    }

    /**
     * Perform automatic message localization. Messages with [[ ]] and without braces accepted.
     * Please use this method statically as in this case it will be correctly indexed.
     *
     * @param string $string
     * @return string
     */
    public static function i18nMessage($string)
    {
        if (
            substr($string, 0, 2) === Translator::I18N_PREFIX
            && substr($string, -2) === Translator::I18N_POSTFIX
        )
        {
            //This string was defined in class attributes
            $string = substr($string, 2, -2);
        }

        return Translator::getInstance()->get(static::i18nBundle(), $string);
    }
}