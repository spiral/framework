<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Core\Traits;

use Interop\Container\ContainerInterface as InteropContainer;
use Spiral\Core\Exceptions\Container\ArgumentException;
use Spiral\Core\Exceptions\Container\InstanceException;


/**
 * Trait provides access to set of shared components (using short bindings).
 *
 * @property-read \Spiral\Core\HippocampusInterface        $memory
 * @property-read \Spiral\Modules\ModuleManager            $modules
 * @property-read \Spiral\Debug\Debugger                   $debugger
 *
 * Dispatchers:
 * @property-read \Spiral\Console\ConsoleDispatcher        $console
 * @property-read \Spiral\Http\HttpDispatcher              $http
 *
 * Shared components:
 * @property-read \Spiral\Encrypter\Encrypter              $encrypter
 * @property-read \Spiral\Files\FileManager                $files
 * @property-read \Spiral\Tokenizer\Tokenizer              $tokenizer
 * @property-read \Spiral\Tokenizer\ClassLocator           $locator
 * @property-read \Spiral\Translator\Translator            $i18n
 * @property-read \Spiral\Views\ViewManager                $views
 * @property-read \Spiral\Storage\StorageManager           $storage
 *
 * Databases and models:
 * @property-read \Spiral\Database\DatabaseManager         $dbal
 * @property-read \Spiral\ODM\ODM                          $odm
 * @property-read \Spiral\ORM\ORM                          $orm
 *
 * Shared entities:
 * @property-read \Spiral\Cache\CacheStore                 $cache
 * @property-read \Spiral\Database\Entities\Database       $db
 * @property-read \Spiral\ODM\Entities\MongoDatabase       $mongo
 *
 * Scope dependent:
 * @property-read \Spiral\Session\SessionStore             $session
 * @property-read \Spiral\Http\Input\InputManager          $input
 * @property-read \Spiral\Http\Cookies\CookieManager       $cookies
 * @property-read \Spiral\Http\Routing\Router              $router
 * @property-read \Psr\Http\Message\ServerRequestInterface $request
 * @property-read \Psr\Http\Message\ResponseInterface      $response
 */
trait SharedTrait
{
    /**
     * @invisible
     * @var InteropContainer
     */
    protected $container = null;

    /**
     * Shortcut to Container get method.
     *
     * @see ContainerInterface::get()
     * @param string $alias
     * @return mixed|null|object
     * @throws InstanceException
     * @throws ArgumentException
     */
    public function __get($alias)
    {
        return $this->container->get($alias);
    }
}