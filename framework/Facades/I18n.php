<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Facades;

use Spiral\Components\Localization\I18nManager;
use Spiral\Core\Facade;

/**
 * @method static bool setTimezone(string $timezone)
 * @method static string getTimezone()
 * @method static setLanguage(string $language)
 * @method static string getLanguage()
 * @method static loadBundle(string $bundle)
 * @method static saveBundle(string $bundle)
 * @method static string normalize(string $string)
 * @method static string get(string $bundle, string $string)
 * @method static string set(string $bundle, string $string, string $translation = '')
 * @method static string pluralize(string $phrase, int $number, bool $numberFormat = true)
 * @method static string getAlias()
 * @method static I18nManager make(array $parameters = array())
 * @method static I18nManager getInstance()
 * @method static array getConfig()
 * @method static array setConfig(array $config)
 */
class I18n extends Facade
{
    /**
     * Facade can statically represent methods of one binded component, such component alias or class
     * name should be defined in bindedComponent constant.
     */
    const COMPONENT = 'i18n';
}