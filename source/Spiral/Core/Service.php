<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Core;

use Spiral\Core\Exceptions\Container\ArgumentException;
use Spiral\Core\Exceptions\Container\InstanceException;

/**
 * Service provides one of the application logic blocks, service must serve to controllers and
 * other logic. Service can declare itself as singleton by implementing SingletonInterface and
 * SINGLETON constant pointing to self.
 *
 * You can declare service logic in boot method, which is going to be executed using container.
 *
 * @property \Spiral\Core\Core                  $core
 * @property \Spiral\Core\Components\Loader     $loader
 * @property \Spiral\Modules\ModuleManager      $modules
 * @property \Spiral\Debug\Debugger             $debugger
 *
 * @property \Spiral\Console\ConsoleDispatcher  $console
 * @property \Spiral\Http\HttpDispatcher        $http
 *
 * @property \Spiral\Cache\CacheProvider        $cache
 * @property \Spiral\Http\Cookies\CookieManager $cookies
 * @property \Spiral\Encrypter\Encrypter        $encrypter
 * @property \Spiral\Http\InputManager          $input
 * @property \Spiral\Files\FileManager          $files
 * @property \Spiral\Session\SessionStore       $session
 * @property \Spiral\Tokenizer\Tokenizer        $tokenizer
 * @property \Spiral\Translator\Translator      $i18n
 * @property \Spiral\Views\ViewManager          $views
 *
 * @property \Spiral\Redis\RedisManager         $redis
 * @property \Spiral\Image\ImageManager         $image
 *
 * @property \Spiral\Database\DatabaseProvider  $dbal
 * @property \Spiral\ODM\ODM                    $odm
 * @property \Spiral\ORM\ORM                    $orm
 */
class Service extends Component
{
    /**
     * @invisible
     * @var ContainerInterface
     */
    protected $container = null;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        if (method_exists($this, 'boot')) {
            $method = new \ReflectionMethod($this, 'boot');

            //Executing boot method
            call_user_func_array([$this, 'boot'], $container->resolveArguments($method));
        }
    }

    /**
     * Shortcut to Container get method.
     *
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