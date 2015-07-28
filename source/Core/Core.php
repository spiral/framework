<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Application;

use Spiral\Core\ConfiguratorInterface;
use Spiral\Core\Container;
use Spiral\Core\CoreInterface;
use Spiral\Core\HippocampusInterface;
use Spiral\Core\SingletonInterface;

class Core extends Container implements
    ConfiguratorInterface,
    HippocampusInterface,
    CoreInterface,
    SingletonInterface
{
    /**
     * Declares to IoC that component instance should be treated as singleton.
     */
    const SINGLETON = self::class;

    /**
     * Default set of core bindings. Can be redefined while constructing core.
     *
     * @invisible
     * @var array
     */
    //    protected $bindings = [
    //        //Core interface bindings
    //        'Spiral\Core\ConfiguratorInterface'     => 'Spiral\Core\Core',
    //        'Spiral\Core\ContainerInterface'        => 'Spiral\Core\Core',
    //        'Spiral\Core\HippocampusInterface'      => 'Spiral\Core\Core',
    //
    //        //Cross component bindings
    //        'Psr\Log\LoggerInterface'               => 'Spiral\Debug\Logger',
    //        'Spiral\Cache\StoreInterface'           => 'Spiral\Cache\CacheStore',
    //        'Spiral\Encrypter\EncrypterInterface'   => 'Spiral\Encrypter\Encrypter',
    //        'Spiral\Tokenizer\TokenizerInterface'   => 'Spiral\Tokenizer\Tokenizer',
    //        'Spiral\Translator\TranslatorInterface' => 'Spiral\Translator\Translator',
    //        'Spiral\Views\ViewsInterface'           => 'Spiral\Views\ViewManager',
    //
    //        //TODO: validator
    //
    //        //Component aliases
    //        'core'                                  => 'Spiral\Core\Core',
    //
    //        //Dispatchers
    //        'http'                                  => 'Spiral\Components\Http\HttpDispatcher',
    //        'console'                               => 'Spiral\Components\Console\ConsoleDispatcher',
    //
    //        //Core components
    //        'loader'                                => 'Spiral\Core\Loader',
    //        'modules'                               => 'Spiral\Components\Modules\ModuleManager',
    //        'file'                                  => 'Spiral\Components\Files\FileManager',
    //        'debug'                                 => 'Spiral\Components\Debug\Debugger',
    //        'tokenizer'                             => 'Spiral\Components\Tokenizer\Tokenizer',
    //        'cache'                                 => 'Spiral\Components\Cache\CacheManager',
    //        'i18n'                                  => 'Spiral\Components\I18n\Translator',
    //        'view'                                  => 'Spiral\Components\View\ViewManager',
    //        'redis'                                 => 'Spiral\Components\Redis\RedisManager',
    //        'encrypter'                             => 'Spiral\Components\Encrypter\Encrypter',
    //        'storage'                               => 'Spiral\Components\Storage\StorageManager',
    //        'dbal'                                  => 'Spiral\Components\DBAL\DatabaseManager',
    //        'orm'                                   => 'Spiral\Components\ORM\ORM',
    //        'odm'                                   => 'Spiral\Components\ODM\ODM',
    //        'cookies'                               => 'Spiral\Components\Http\Cookies\CookieManager',
    //        'session'                               => 'Spiral\Components\Session\SessionStore',
    //        'input'                                 => 'Spiral\Components\Http\InputManager',
    //
    //        'request'                               => 'Psr\Http\Message\ServerRequestInterface',
    //
    //        //Pre-bundled, but supplied as external modules with common class
    //        'image'                                 => 'Spiral\Components\Image\ImageManager',
    //    ];

}