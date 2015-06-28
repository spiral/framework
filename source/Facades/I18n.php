<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Facades;

use Spiral\Components\I18n\PluralizerInterface;
use Spiral\Components\I18n\Translator;
use Spiral\Core\Container;
use Spiral\Core\Facade;

/**
 * @method static void setLanguage($language)
 * @method static string getLanguage()
 * @method static PluralizerInterface getPluralizer($language = '')
 * @method static string normalize($string)
 * @method static string get($bundle, $string)
 * @method static string set($bundle, $string, $translation = '')
 * @method static string pluralize($phrase, $number, $numberFormat = true)
 * @method static Translator make($parameters = [], Container $container = null)
 * @method static Translator getInstance(Container $container = null)
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