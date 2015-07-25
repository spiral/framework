<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Core;

use Spiral\Core\Component\EventsTrait;
use Spiral\Core\Component\SingletonTrait;
use Spiral\Core\Dispatcher\ClientException;
use Spiral\Components;
use Spiral\Components\Debug\Snapshot;
use Spiral\Components\Files\FileManager;
use Spiral\Components\Http\HttpDispatcher;
use Spiral\Components\Console\ConsoleDispatcher;

/**
 * @property Components\Http\HttpDispatcher           $http
 * @property Components\Console\ConsoleDispatcher     $console
 * @property Loader                                   $loader
 * @property Components\Modules\ModuleManager         $modules
 * @property Components\Files\FileManager             $file
 * @property Components\Debug\Debugger                $debug
 * @property Components\Tokenizer\Tokenizer           $tokenizer
 * @property Components\Cache\CacheManager            $cache
 * @property Components\I18n\Translator               $i18n
 * @property Components\View\ViewManager              $view
 * @property Components\Redis\RedisManager            $redis
 * @property Components\Encrypter\Encrypter           $encrypter
 * @property Components\Image\ImageManager            $image
 * @property Components\Storage\StorageManager        $storage
 * @property Components\DBAL\DatabaseManager          $dbal
 * @property Components\ODM\ODM                       $odm
 * @property Components\ORM\ORM                       $orm
 *
 * @property \Psr\Http\Message\ServerRequestInterface $request
 * @property Components\Http\Cookies\CookieManager    $cookies
 * @property Components\Session\SessionStore          $session
 * @property Components\Http\Router\Router            $router
 * @property Components\Http\InputManager             $input
 */
class Core extends Container implements CoreInterface, ConfiguratorInterface, RuntimeCacheInterface
{
    /**
     * Singleton and events.s
     */
    use SingletonTrait, EventsTrait;

    /**
     * Declares to IoC that component instance should be treated as singleton.
     */
    const SINGLETON = __CLASS__;

    /**
     * Spiral Core version.
     */
    const VERSION = '0.9.0b';

    /**
     * Extension used for configuration files, ".php" by default.
     */
    const CONFIGS_EXTENSION = 'php';

    /**
     * Some environment constants to use to produce more clean code with less magic values.
     */
    const DEVELOPMENT = 'development';
    const PRODUCTION  = 'production';
    const STAGING     = 'staging';
    const TESTING     = 'testing';

    /**
     * Default set of core bindings. Can be redefined while constructing core.
     *
     * @invisible
     * @var array
     */
    protected $bindings = [
        'core'      => 'Spiral\Core\Core',

        //Dispatchers
        'http'      => 'Spiral\Components\Http\HttpDispatcher',
        'console'   => 'Spiral\Components\Console\ConsoleDispatcher',

        //Core components
        'loader'    => 'Spiral\Core\Loader',
        'modules'   => 'Spiral\Components\Modules\ModuleManager',
        'file'      => 'Spiral\Components\Files\FileManager',
        'debug'     => 'Spiral\Components\Debug\Debugger',
        'tokenizer' => 'Spiral\Components\Tokenizer\Tokenizer',
        'cache'     => 'Spiral\Components\Cache\CacheManager',
        'i18n'      => 'Spiral\Components\I18n\Translator',
        'view'      => 'Spiral\Components\View\ViewManager',
        'redis'     => 'Spiral\Components\Redis\RedisManager',
        'encrypter' => 'Spiral\Components\Encrypter\Encrypter',
        'storage'   => 'Spiral\Components\Storage\StorageManager',
        'dbal'      => 'Spiral\Components\DBAL\DatabaseManager',
        'orm'       => 'Spiral\Components\ORM\ORM',
        'odm'       => 'Spiral\Components\ODM\ODM',
        'cookies'   => 'Spiral\Components\Http\Cookies\CookieManager',
        'session'   => 'Spiral\Components\Session\SessionStore',
        'input'     => 'Spiral\Components\Http\InputManager',

        'request'   => 'Psr\Http\Message\ServerRequestInterface',

        //Pre-bundled, but supplied as external modules with common class
        'image'     => 'Spiral\Components\Image\ImageManager',
    ];

    /**
     * Current environment id (name), that value can be used directly in code by accessing
     * Core::getEnvironment() or Application::getEnvironment() (if you using that name), environment
     * can be changed at any moment via setEnvironment() method. Environment used to merge configuration
     * files (default + environment), so changing this value in a middle of application will keep
     * already initiated components binded to previous values.
     *
     * @var string
     */
    protected $environment = null;

    /**
     * Set of directory aliases defined during application bootstrap and in index.php file. Such
     * directory will be automatically resolved during reading config files and can be accessed using
     * Core::directory() method. You can redefine any directory at any moment of time using same method.
     *
     * You can additionally use short function directory() to get or assign directory alias.
     *
     * @var array
     */
    protected $directories = [
        'libraries'   => null,
        'framework'   => null,
        'application' => null,
        'runtime'     => null,
        'config'      => null,
        'cache'       => null
    ];

    /**
     * Set of components to be pre-loaded before bootstrap method. By default spiral load Loader,
     * Modules and I18n components.
     *
     * @var array
     */
    protected $autoload = ['loader', 'modules'];

    /**
     * Current application id, should be unique value between your environments, that value can be
     * used in cache adapters to isolate multiple spiral instances, it's recommend to keep applicationID
     * unique in terms of server. That value also will be used as postfix for all cache configurations
     * and application data files.
     *
     * @var string
     */
    protected $applicationID = '';

    /**
     * Current dispatcher instance response for application flow processing.
     *
     * @var DispatcherInterface
     */
    protected $dispatcher = null;

    /**
     * Initial application timezone. Can be redefined in child core realization. You can change
     * timezones in runtime by using setTimezone() method.
     *
     * @var string
     */
    protected $timezone = 'UTC';

    /**
     * Core constructor can be redefined by custom application and called as first function inside
     * Core::start() or Application::start(). Core instance will be automatically binded for future
     * use under alias "core" and can be passed to components, models and controllers using IoC container.
     **
     * By default spiral will to check file named "environment.php" under application data directory,
     * such file should contain simple php code to return environment id.
     *
     * @param array $directories Initial set of directories. Should include root and application aliases.
     */
    public function __construct(array $directories)
    {
        $this->directories = $directories;

        $this->directories['config'] = $this->directories['application'] . '/config';
        $this->directories['runtime'] = $this->directories['application'] . '/runtime';
        $this->directories['cache'] = $this->directories['runtime'] . '/cache';

        if (empty($this->environment))
        {
            $filename = $this->directory('runtime') . '/environment.php';
            $this->setEnvironment(file_exists($filename) ? (require $filename) : self::DEVELOPMENT);
        }

        /**
         * Timezones are really important.
         */
        date_default_timezone_set($this->timezone);
    }

    /**
     * Set directory alias value.
     *
     * @param string $alias Directory alias, ie. "framework".
     * @param string $value Directory path without ending slash.
     * @return null
     */
    public function setDirectory($alias, $value)
    {
        return $this->directories[$alias] = $value;
    }

    /**
     * Get directory value.
     *
     * @param string $alias Directory alias, ie. "framework".
     * @return null
     */
    public function directory($alias)
    {
        return $this->directories[$alias];
    }

    /**
     * Get all declared directory aliases.
     *
     * @return array
     */
    public function getDirectories()
    {
        return $this->directories;
    }

    /**
     * Current application id, should be unique value between your environments, that value can be
     * used in cache adapters to isolate multiple spiral instances, it's recommend to keep applicationID
     * unique in terms of server. By default value generated using current environment and name of
     * directory where project files located.
     *
     * @return mixed
     */
    public function applicationID()
    {
        return $this->applicationID;
    }

    /**
     * Environment can be changed in runtime, all initiated components will use existed configurations,
     * you will have to reload binded components to ensure that new configuration data used.
     *
     * @param mixed $environment
     * @param bool  $regenerateID
     */
    public function setEnvironment($environment, $regenerateID = true)
    {
        $this->environment = $environment;
        if ($regenerateID)
        {
            $this->applicationID = abs(crc32($this->directory('root') . $this->environment));
        }
    }

    /**
     * Current application environment used to merge configurations or define application behaviour.
     *
     * @return string
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * Change application timezone. Method will return false if invalid timezone identifier provided.
     * Validate value with is::timezone validator before applying it to handle user errors.
     *
     * @param string $timezone Valid PHP timezone identifier.
     * @return bool
     */
    public function setTimezone($timezone)
    {
        try
        {
            date_default_timezone_set($timezone);
        }
        catch (\Exception $exception)
        {
            return false;
        }

        $this->timezone = $timezone;

        return true;
    }

    /**
     * Currently selected timezone, valid PHP timezone identifier.
     *
     * @return string
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * Application enterpoint, should be called once in index.php file. That method will declare
     * runtime version of core, initiate loader and run bootstrap() method. Rest of application flow
     * will be controlled using Dispatcher instance which can be declared in $core->getDispatcher().
     * Use Application->start() to start application.
     *
     * @param array $directories Spiral directories should include root, libraries, config and runtime
     *                           directories.
     * @return $this
     * @throws CoreException
     */
    public static function init(array $directories)
    {
        /**
         * @var Core $core
         */
        $core = new static($directories + ['framework' => dirname(__DIR__)]);

        $core->bindings[get_called_class()]
            = $core->bindings[self::SINGLETON]
            = $core->bindings['Spiral\Core\CoreInterface']
            = $core->bindings['Spiral\Core\ConfiguratorInterface']
            = $core->bindings['Spiral\Core\RuntimeCacheInterface']
            = $core;

        /**
         * Making application to be instance of global container.
         */
        self::$instance = $core;

        //Error and exception handlers
        set_error_handler([$core, 'errorHandler']);
        set_exception_handler([$core, 'handleException']);
        register_shutdown_function([$core, 'shutdownHandler']);

        foreach ($core->autoload as $module)
        {
            $core->get($module, compact('core'));
        }

        //Bootstrapping
        $core->bootstrap();

        return $core;
    }

    /**
     * Bootstrapping. Most of code responsible for routes, endpoints, events and other application
     * preparations should located in this method.
     */
    public function bootstrap()
    {
        if (file_exists(directory('application') . '/bootstrap.php'))
        {
            require directory('application') . '/bootstrap.php';
        }
    }

    /**
     * Method used by core to switch between HTTP and CLI dispatchers, can also be used in other
     * application parts to determinate PHP environment.
     *
     * @return bool
     */
    public static function isConsole()
    {
        return (PHP_SAPI === 'cli');
    }

    /**
     * Should return appropriate to use dispatcher, by default implementation core will select
     * dispatcher based on php environment, HTTP component will be used for web and CLI will be
     * constructed while calling from console. This method can be redefined to introduce new dispatchers
     * or logic to select one. Newly constructed dispatched will be binded under "dispatcher" alias.
     *
     * @return DispatcherInterface
     */
    protected function createDispatcher()
    {
        $dispatcher = $this->isConsole() ? ConsoleDispatcher::SINGLETON : HttpDispatcher::SINGLETON;

        return $this->get($dispatcher, ['core' => $this]);
    }

    /**
     * Starting application processing by giving control to selected dispatcher.
     *
     * @param DispatcherInterface $dispatcher Forced application dispatcher.
     */
    public function start(DispatcherInterface $dispatcher = null)
    {
        $this->dispatcher = !empty($dispatcher) ? $dispatcher : $this->createDispatcher();
        $this->dispatcher->start($this);
    }

    /**
     * Call controller method by fully specified or short controller name, action and addition
     * options such as default controllers namespace, default name and postfix.
     *
     * @param string $controller Controller name, or class, or name with namespace prefix.
     * @param string $action     Controller action, empty by default (controller will use default action).
     * @param array  $parameters Additional methods parameters.
     * @return mixed
     * @throws ClientException
     * @throws CoreException
     */
    public function callAction($controller, $action = '', array $parameters = [])
    {
        if (!class_exists($controller))
        {
            throw new ClientException(ClientException::NOT_FOUND);
        }

        //Initiating controller with all required dependencies
        $controller = $this->get($controller);
        if (!$controller instanceof ControllerInterface)
        {
            throw new ClientException(404, "Not a valid controller.");
        }

        return $controller->callAction($action, $parameters);
    }

    /**
     * Handle error message handlers, will convert error to exception which will be automatically
     * handled by active dispatcher. Can be also used to force ErrorException via static method (if
     * anyone need it).
     *
     * @param int    $code    Error code.
     * @param string $message Error message.
     * @param string $filename
     * @param int    $line
     * @throws \ErrorException
     */
    public function errorHandler($code, $message, $filename = '', $line = 0)
    {
        ini_set('display_errors', false);
        throw new \ErrorException($message, $code, 0, $filename, $line);
    }

    /**
     * Automatic handler for fatal and syntax error, this error can't be handled via default error
     * handler.
     */
    public function shutdownHandler()
    {
        if ($error = error_get_last())
        {
            $this->handleException(new \ErrorException(
                $error['message'],
                $error['type'],
                0,
                $error['file'],
                $error['line']
            ));
        }
    }

    /**
     * Exception handling, by default spiral will handle exception using Debug component and pass
     * ExceptionSnapshot to active dispatcher.
     *
     * @param \Exception $exception
     */
    public function handleException(\Exception $exception)
    {
        restore_error_handler();
        restore_exception_handler();

        if ($snapshot = $this->debug->handleException($exception))
        {
            if ($snapshot = $this->event('exception', $snapshot))
            {
                $this->dispatchSnapshot($snapshot);
            }
        }
    }

    /**
     * Pass exception handling to currently active dispatcher or render exception content.
     *
     * @param Snapshot $snapshot
     */
    protected function dispatchSnapshot(Snapshot $snapshot)
    {
        if (!empty($this->dispatcher))
        {
            $this->dispatcher->handleException($snapshot);

            return;
        }

        //Direct echoing to client
        echo $snapshot;
    }

    /**
     * Load data previously saved to application cache, if file is not exists null will be returned.
     * This method can be replaced by Core Traits to use different ways to store data like APC.
     *
     * @param string $name      Filename without .php
     * @param string $directory Application cache directory will be used by default.
     * @param string $realPath  Generated file location will be stored in this variable.
     * @return mixed|array
     */
    public function loadData($name, $directory = null, &$realPath = null)
    {
        if (!file_exists($realPath = $this->makeFilename($name, $directory)))
        {
            return null;
        }

        try
        {
            return (require $realPath);
        }
        catch (\ErrorException $exception)
        {
            return null;
        }
    }

    /**
     * Save runtime data to application cache, previously saved file can be removed or rewritten at
     * any moment. Cache is determined by current applicationID and different for different environments.
     * This method can be replaced by Core Traits to use different ways to store data like APC.
     *
     * All data stored using var_export() function, be aware of having to many write requests, however
     * read will be optimized by PHP using OPCache.
     *
     * File permission specified in File::RUNTIME to make file readable and writable for both web and
     * CLI sessions.
     *
     * @param string $name      Filename without .php
     * @param mixed  $data      Data to be stored, any format supported by var_export().
     * @param string $directory Application cache directory will be used by default.
     * @return bool|string
     */
    public function saveData($name, $data, $directory = null)
    {
        $name = $this->makeFilename($name, $directory);

        //This is required as FileManager system component and can be called pretty early
        $file = FileManager::getInstance($this);

        $data = '<?php return ' . var_export($data, true) . ';';
        if ($file->write($name, $data, FileManager::RUNTIME, true))
        {
            return $name;
        }

        return false;
    }

    /**
     * Load configuration files specified in application config directory. Config file may have
     * extension, locked under Core::getEnvironment() directory, this section will replace original
     * config while application is under giver environment. All config files with merged environment
     * stored under cache directory.
     *
     * @param string $config Config filename (no .php)
     * @return array
     * @throws CoreException
     */
    public function getConfig($config)
    {
        $filename = $this->directories['config'] . '/' . $config . '.' . self::CONFIGS_EXTENSION;

        //Cached filename
        $cached = str_replace(['/', '\\'], '-', 'config-' . $config);

        //Cached configuration
        if (($data = $this->loadData($cached, null, $cachedFilename)) === null)
        {
            if (!file_exists($filename))
            {
                throw new CoreException(
                    "Unable to load '{$config}' configuration, file not found."
                );
            }

            $data = (require $filename);

            $environment = $this->directories['config']
                . '/' . $this->getEnvironment() . '/' . $config . '.' . self::CONFIGS_EXTENSION;

            if (file_exists($environment))
            {
                $data = array_merge($data, (require $environment));
            }

            $data = $this->event('config', compact('config', 'data', 'filename'))['data'];
            $this->saveData($cached, $data, null, true);

            return $data;
        }

        if (!file_exists($filename))
        {
            throw new CoreException("Unable to load '{$config}' configuration, file not found.");
        }

        if (filemtime($cachedFilename) < filemtime($filename))
        {
            file_exists($cachedFilename) && unlink($cachedFilename);

            //Configuration were updated, reloading
            return $this->getConfig($config);
        }

        return $data;
    }

    /**
     * Get extension to use for runtime data or configuration cache, all file in cache directory will
     * additionally get applicationID postfix.
     *
     * @param string $name      Runtime data file name (without extension).
     * @param string $directory Directory to store data in.
     * @return string
     */
    protected function makeFilename($name, $directory = null)
    {
        $name = str_replace(['/', '\\'], '-', $name);

        if ($directory)
        {
            return rtrim($directory, '/') . '/' . $name . '.' . static::RUNTIME_EXTENSION;
        }

        return $this->directories['cache']
        . "/$name-{$this->applicationID}" . '.' . static::RUNTIME_EXTENSION;
    }
}