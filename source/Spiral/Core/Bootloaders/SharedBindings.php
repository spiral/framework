<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Core\Bootloaders;

use Psr\Http\Message\ServerRequestInterface;
use Spiral\Core\Exceptions\CoreException;
use Spiral\Core\Exceptions\SugarException;
use Spiral\Http\Routing\RouteInterface;

/**
 * Shared components and short bindings.
 */
class SharedBindings extends Bootloader
{
    /**
     * No need to boot, all cached.
     */
    const BOOT = false;

    /**
     * @var array
     */
    protected $bindings = [
        //Core components (see SharedTrait)
        'memory'            => 'Spiral\Core\HippocampusInterface',
        'modules'           => 'Spiral\Modules\ModuleManager',
        'debugger'          => 'Spiral\Debug\Debugger',

        //Container
        'container'         => 'Spiral\Core\ContainerInterface',

        //Dispatchers
        'http'              => 'Spiral\Http\HttpDispatcher',
        'console'           => 'Spiral\Console\ConsoleDispatcher',

        //Shared components
        'files'             => 'Spiral\Files\FileManager',
        'tokenizer'         => 'Spiral\Tokenizer\Tokenizer',
        'locator'           => 'Spiral\Tokenizer\ClassLocator',
        'invocationLocator' => 'Spiral\Tokenizer\InvocationLocator',
        'translator'        => 'Spiral\Translator\Translator',
        'views'             => 'Spiral\Views\ViewManager',
        'storage'           => 'Spiral\Storage\StorageManager',

        //Databases and models
        'dbal'              => 'Spiral\Database\DatabaseManager',
        'orm'               => 'Spiral\ORM\ORM',
        'odm'               => 'Spiral\ODM\ODM',

        //Entities
        'encrypter'         => 'Spiral\Encrypter\Encrypter',
        'cache'             => 'Spiral\Cache\CacheStore',
        'db'                => 'Spiral\Database\Entities\Database',
        'mongo'             => 'Spiral\ODM\Entities\MongoDatabase',

        //Http scope dependent
        'session'           => 'Spiral\Session\SessionStore',
        'cookies'           => 'Spiral\Http\Cookies\CookieManager',
        'router'            => 'Spiral\Http\Routing\Router',
        'request'           => 'Psr\Http\Message\ServerRequestInterface',

        //Http scope depended data routes and wrappers
        'input'             => 'Spiral\Http\Input\InputManager',
        'responses'         => 'Spiral\Http\Responses\Responder',

        //Active route thought request attribute
        'route'             => [self::class, 'activeRoute']
    ];

    /**
     * @param ServerRequestInterface $request
     * @return RouteInterface
     */
    public function activeRoute(ServerRequestInterface $request)
    {
        $route = $request->getAttribute('route');

        if (!$route instanceof RouteInterface) {
            throw new SugarException("Unable to resolve active route using active request.");
        }

        return $request->getAttribute('route');
    }
}