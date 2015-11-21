<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Core\Spiral;

use Spiral\Core\ServiceProviders\ServiceProvider;

/**
 * Shared components and short bindings.
 */
class ShortBindings extends ServiceProvider
{
    /**
     * @var array
     */
    protected $bindings = [
        //Core components (see SharedTrait)
        'memory'    => 'Spiral\Core\HippocampusInterface',
        'modules'   => 'Spiral\Modules\ModuleManager',
        'debugger'  => 'Spiral\Debug\Debugger',

        //Dispatchers
        'http'      => 'Spiral\Http\HttpDispatcher',
        'console'   => 'Spiral\Console\ConsoleDispatcher',

        //Shared components
        'encrypter' => 'Spiral\Encrypter\Encrypter',
        'files'     => 'Spiral\Files\FileManager',
        'tokenizer' => 'Spiral\Tokenizer\Tokenizer',
        'locator'   => 'Spiral\Tokenizer\ClassLocator',
        'i18n'      => 'Spiral\Translator\Translator',
        'views'     => 'Spiral\Views\ViewManager',
        'storage'   => 'Spiral\Storage\StorageManager',

        //Databases and models
        'dbal'      => 'Spiral\Database\DatabaseManager',
        'orm'       => 'Spiral\ORM\ORM',
        'odm'       => 'Spiral\ODM\ODM',

        //Shared entities
        'cache'     => 'Spiral\Cache\CacheStore',
        'db'        => 'Spiral\Database\Entities\Database',
        'mongo'     => 'Spiral\ODM\Entities\MongoDatabase',

        //Scope dependent
        'session'   => 'Spiral\Session\SessionStore',
        'input'     => 'Spiral\Http\Input\InputManager',
        'cookies'   => 'Spiral\Http\Cookies\CookieManager',
        'router'    => 'Spiral\Http\Routing\Router',
        'request'   => 'Psr\Http\Message\ServerRequestInterface',
        'response'  => 'Psr\Http\Message\ResponseInterface',
    ];
}