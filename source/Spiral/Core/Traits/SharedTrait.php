<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Core\Traits;

use Spiral\Core\ContainerInterface;
use Spiral\Core\Exceptions\Container\ArgumentException;
use Spiral\Core\Exceptions\Container\InstanceException;

/**
 * Trait provides access to set of shared components (using short bindings).
 *
 * @property \Spiral\Core\Core                        $core
 * @property \Spiral\Core\Components\Loader           $loader
 * @property \Spiral\Modules\ModuleManager            $modules
 * @property \Spiral\Debug\Debugger                   $debugger
 *
 * @property \Spiral\Console\ConsoleDispatcher        $console
 * @property \Spiral\Http\HttpDispatcher              $http
 *
 * @property \Spiral\Cache\CacheManager               $cache
 * @property \Spiral\Encrypter\Encrypter              $encrypter
 * @property \Spiral\Http\InputManager                $input
 * @property \Spiral\Files\FileManager                $files
 * @property \Spiral\Session\SessionStore             $session
 * @property \Spiral\Tokenizer\Tokenizer              $tokenizer
 * @property \Spiral\Translator\Translator            $i18n
 * @property \Spiral\Views\ViewManager                $views
 * @property \Spiral\Storage\StorageManager           $storage
 *
 * @property \Spiral\Redis\RedisManager               $redis
 * @property \Spiral\Image\ImageManager               $images
 *
 * @property \Spiral\RBAC\RBACManager                 $rbac
 * @property \Spiral\RBAC\Guard                       $guard
 *
 * @property \Spiral\Database\DatabaseManager         $dbal
 * @property \Spiral\ODM\ODM                          $odm
 * @property \Spiral\ORM\ORM                          $orm
 *
 * @property \Spiral\Http\Cookies\CookieManager       $cookies  Scope depended.
 * @property \Spiral\Http\Routing\Router              $router   Scope depended.
 * @property \Psr\Http\Message\ServerRequestInterface $request  Scope depended.
 */
trait SharedTrait
{
    /**
     * @invisible
     * @var ContainerInterface
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