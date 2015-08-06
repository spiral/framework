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
use Spiral\Core\Exceptions\ControllerException;
use Spiral\Debug\Traits\BenchmarkTrait;

/**
 * Basic application controller class. Implements method injections and simplified access to
 * container bindings.
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
 */
abstract class Controller extends Component implements ControllerInterface
{
    /**
     * To benchmark action execution time.
     */
    use BenchmarkTrait;

    /**
     * Action method prefix value.
     *
     * @var string
     */
    const ACTION_PREFIX = '';

    /**
     * Default action to run.
     *
     * @var string
     */
    protected $defaultAction = 'index';

    /**
     * Set of parameters passed into callAction method.
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * Container instance to be associated as moment of callAction call.
     *
     * @var ContainerInterface
     */
    protected $container = null;

    /**
     * {@inheritdoc}
     */
    public function callAction(ContainerInterface $container, $action = '', array $parameters = [])
    {
        //Action should include prefix and be always specified
        $action = static::ACTION_PREFIX . (!empty($action) ? $action : $this->defaultAction);

        if (!method_exists($this, $action)) {
            throw new ControllerException(
                "No such action '{$action}'.",
                ControllerException::BAD_ACTION
            );
        }

        $reflection = new \ReflectionMethod($this, $action);

        if (
            $reflection->isStatic()
            || !$reflection->isPublic()
            || !$reflection->isUserDefined()
            || $reflection->getDeclaringClass()->getName() == __CLASS__
        ) {
            throw new ControllerException(
                "Action '{$action}' can not be executed.",
                ControllerException::BAD_ACTION
            );
        }

        $this->container = $container;
        $this->parameters = $parameters;

        try {
            //Getting set of arguments should be sent to requested method
            $arguments = $this->container->resolveArguments($reflection, $parameters);
        } catch (ArgumentException $exception) {
            throw new ControllerException(
                "Missing/invalid parameter '{$exception->getParameter()->name}'.",
                ControllerException::BAD_ARGUMENT
            );
        }

        if (($result = $this->preAction($reflection, $arguments)) !== null) {
            //Got filtered.
            return $result;
        }

        $this->benchmark($action = $reflection->getName());
        $result = $reflection->invokeArgs($this, $arguments);
        $this->benchmark($action);

        return $this->postAction($result, $reflection, $arguments);
    }

    /**
     * Executed before action call, can return non empty value to be sent to client.
     *
     * @param \ReflectionMethod $method
     * @param array             $arguments
     * @return mixed
     */
    protected function preAction(\ReflectionMethod $method, array $arguments)
    {
        return null;
    }

    /**
     * Executed after action with action result to be filtered. Must return result in normal flow.
     *
     * @param mixed             $result
     * @param \ReflectionMethod $method
     * @param array             $arguments
     * @return mixed
     */
    protected function postAction($result, \ReflectionMethod $method, array $arguments)
    {
        return $result;
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