<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Core;

use Spiral\Core\Component\LoggerTrait;
use Spiral\Core\Dispatcher\ClientException;
use Spiral\Components;

/**
 * @property Core                                  $core
 * @property Components\Http\HttpDispatcher        $http
 * @property Components\Console\ConsoleDispatcher  $console
 * @property Loader                                $loader
 * @property Components\Modules\ModuleManager      $modules
 * @property Components\Files\FileManager          $file
 * @property Components\Debug\Debugger             $debug
 * @property Components\Tokenizer\Tokenizer        $tokenizer
 * @property Components\Cache\CacheManager         $cache
 * @property Components\Localization\I18nManager   $i18n
 * @property Components\View\View                  $view
 * @property Components\Redis\RedisManager         $redis
 * @property Components\Encrypter\Encrypter        $encrypter
 * @property Components\Image\ImageManager         $image
 * @property Components\Storage\StorageManager     $storage
 * @property Components\DBAL\DatabaseManager       $dbal
 * @property Components\ORM\ORM                    $orm
 * @property Components\ODM\ODM                    $odm
 *
 * @property Components\Http\Request               $request
 * @property Components\Http\Cookies\CookieManager $cookies
 */
class Controller extends Component implements ControllerInterface
{
    /**
     * Can be potentially used in controller.
     */
    use LoggerTrait;

    /**
     * Default action to run. This action will be performed if dispatcher didn't specified another
     * action to run.
     *
     * @var string
     */
    protected $defaultAction = 'index';

    /**
     * Performing controller action. This method should either return response object or string, or
     * any other type supported by specified dispatcher. This method can be overwritten in child
     * controller to force some specific Response or modify output from every controller action.
     *
     * @param string $action     Method name.
     * @param array  $parameters Set of parameters to populate controller method.
     * @return mixed
     * @throws ClientException
     */
    public function callAction($action = '', array $parameters = array())
    {
        if (!method_exists($this, $action = $action ?: $this->defaultAction))
        {
            throw new ClientException(ClientException::NOT_FOUND);
        }

        $reflection = new \ReflectionMethod($this, $action);
        if (!$reflection->isPublic() || $reflection->isStatic() || !$reflection->isUserDefined() || $reflection->getDeclaringClass()->getName() == __CLASS__)
        {
            throw new ClientException(ClientException::NOT_FOUND);
        }

        //Getting set of arguments should be sent to requested method
        $arguments = $this->core->resolveArguments($reflection, $parameters, true);
        foreach ($reflection->getParameters() as $parameter)
        {
            if (!array_key_exists($parameter->getPosition(), $arguments) && !$parameter->isOptional())
            {
                throw new ClientException("Missing parameter '{$parameter->name}'.");
            }
        }

        $action = $reflection->getName();

        benchmark(get_called_class(), $action);
        $response = $reflection->invokeArgs($this, $arguments);
        benchmark(get_called_class(), $action);

        return $response;
    }

    /**
     * An alias for Container::get() method to retrieve components by their alias.
     *
     * @param string $name Binding or component name/alias.
     * @return Component
     */
    public function __get($name)
    {
        return Container::get($name);
    }
}