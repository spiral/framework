<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Core\Traits;

use Spiral\Core\Exceptions\Container\ArgumentException;
use Spiral\Core\Exceptions\Container\InstanceException;
use Spiral\Core\InteropContainerInterface;

/**
 * Trait provides access to set of shared components (using short bindings).
 *
 * @property \Spiral\Core\HippocampusInterface        $memory
 * @property \Spiral\Modules\ModuleManager            $modules
 * @property \Spiral\Debug\Debugger                   $debugger
 *
 * Dispatchers:
 * @property \Spiral\Console\ConsoleDispatcher        $console
 * @property \Spiral\Http\HttpDispatcher              $http
 *
 * Shared components:
 * @property \Spiral\Encrypter\Encrypter              $encrypter
 * @property \Spiral\Files\FileManager                $files
 * @property \Spiral\Session\SessionStore             $session
 * @property \Spiral\Tokenizer\Tokenizer              $tokenizer
 * @property \Spiral\Tokenizer\ClassLocator           $locator
 * @property \Spiral\Translator\Translator            $i18n
 * @property \Spiral\Views\ViewManager                $views
 * @property \Spiral\Storage\StorageManager           $storage
 *
 * Databases and models:
 * @property \Spiral\Database\DatabaseManager         $dbal
 * @property \Spiral\ODM\ODM                          $odm
 * @property \Spiral\ORM\ORM                          $orm
 *
 * Shared entities:
 * @property \Spiral\Cache\CacheStore                 $cache
 * @property \Spiral\Database\Entities\Database       $db
 * @property \Spiral\ODM\Entities\MongoDatabase       $mongo
 *
 * Scope dependent:
 * @property \Spiral\Http\InputRouter                 $input
 * @property \Spiral\Http\Cookies\CookieManager       $cookies
 * @property \Spiral\Http\Headers\HeaderManager       $headers
 * @property \Spiral\Http\Routing\Router              $router
 * @property \Psr\Http\Message\ServerRequestInterface $request
 * @property \Psr\Http\Message\ResponseInterface      $response
 */
trait SharedTrait
{
    /**
     * @invisible
     * @var InteropContainerInterface
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