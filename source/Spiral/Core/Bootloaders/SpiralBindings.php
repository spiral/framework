<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Core\Bootloaders;

use Psr\Http\Message\ServerRequestInterface;
use Spiral\Core\Exceptions\ScopeException;
use Spiral\Http\HttpDispatcher;
use Spiral\Http\Routing\RouteInterface;

/**
 * Shared components and short bindings.
 */
class SpiralBindings extends Bootloader
{
    /**
     * No need to boot, all cached.
     */
    const BOOT = false;

    /**
     * @var array
     */
    const BINDINGS = [
        //How to resolve log instances
        'Psr\Log\LoggerInterface'            => ['Spiral\Debug\LogsInterface', 'getLogger'],
        'Monolog\Logger'                     => ['Spiral\Debug\LogsInterface', 'getLogger'],

        //Core components (see SharedTrait)
        'memory'                             => 'Spiral\Core\MemoryInterface',
        'container'                          => 'Spiral\Core\ContainerInterface',

        //Logging
        'logs'                               => 'Spiral\Debug\LogsInterface',

        //Dispatchers
        'http'                               => 'Spiral\Http\HttpDispatcher',
        'console'                            => 'Spiral\Console\ConsoleDispatcher',

        //Alias for console
        'commands'                           => 'Spiral\Console\ConsoleDispatcher',

        //Shared components
        'files'                              => 'Spiral\Files\FilesInterface',
        'tokenizer'                          => 'Spiral\Tokenizer\TokenizerInterface',
        'locator'                            => 'Spiral\Tokenizer\ClassesInterface',
        'invocationLocator'                  => 'Spiral\Tokenizer\InvocationsInterface',
        'storage'                            => 'Spiral\Storage\StorageInterface',

        //Concrete for now
        'views'                              => 'Spiral\Views\ViewManager',
        'translator'                         => 'Spiral\Translator\Translator',

        //Databases and models
        'dbal'                               => 'Spiral\Database\DatabaseManager',
        'orm'                                => 'Spiral\ORM\ORM',
        'odm'                                => 'Spiral\ODM\ODM',

        //Encryption
        'encrypter'                          => 'Spiral\Encrypter\EncrypterInterface',

        //Concrete for now, replace with better interface in future
        'db'                                 => 'Spiral\Database\Entities\Database',
        'mongo'                              => 'Spiral\ODM\Entities\MongoDatabase',

        //Http scope dependent
        'cookies'                            => 'Spiral\Http\Cookies\CookieQueue',
        'session'                            => 'Spiral\Session\SessionInterface',

        //Pagination manager
        'paginators'                         => 'Spiral\Pagination\PaginatorsInterface',

        //Http scope depended data routes and wrappers
        'request'                            => 'Psr\Http\Message\ServerRequestInterface',
        'input'                              => 'Spiral\Http\Request\InputManager',

        //Response and response wrappers
        'response'                           => 'Spiral\Http\Response\ResponseWrapper',

        //Short aliases
        'route'                              => 'Spiral\Http\Routing\RouteInterface',

        //Security component
        'permissions'                        => 'Spiral\Security\PermissionsInterface',
        'rules'                              => 'Spiral\Security\RulesInterface',

        //Scope depended
        'actor'                              => 'Spiral\Security\ActorInterface',

        //Default router is http specific
        'Spiral\Http\Routing\RouterInterface' => [HttpDispatcher::class, 'getRouter'],
        'router'                             => 'Spiral\Http\Routing\RouterInterface',

        //Thought request attributes
        'Spiral\Http\Routing\RouteInterface' => [self::class, 'activeRoute'],
    ];

    /**
     * @var array
     */
    const SINGLETONS = [
        'Cocur\Slugify\SlugifyInterface' => 'Cocur\Slugify\Slugify'
    ];

    /**
     * @param ServerRequestInterface $request
     *
     * @return RouteInterface
     */
    public function activeRoute(ServerRequestInterface $request = null)
    {
        if (empty($request)) {
            throw new ScopeException("No active request found");
        }

        $route = $request->getAttribute('route');

        if (!$route instanceof RouteInterface) {
            throw new ScopeException("Unable to resolve active route using active request");
        }

        return $route;
    }
}
