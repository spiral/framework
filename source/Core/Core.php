<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Core;

use Spiral\Console\ConsoleDispatcher;
use Spiral\Files\FilesInterface;
use Spiral\Http\HttpDispatcher;

/**
 * Spiral Application specific bindings.
 *
 * @property \Spiral\Core\Core                  $core
 * @property \Spiral\Core\Loader                $loader
 * @property \Spiral\Modules\ModuleManager      $modules
 *
 * @property \Spiral\Console\ConsoleDispatcher  $console
 * @property \Spiral\Http\HttpDispatcher        $http
 *
 * @property \Spiral\Cache\CacheManager         $cache
 * @property \Spiral\Http\Cookies\CookieManager $cookies
 * @property \Spiral\Database\DatabaseManager   $dbal
 * @property \Spiral\Encrypter\Encrypter        $encrypter
 * @property \Spiral\Http\InputManager          $input
 * @property \Spiral\Files\FileManager          $files
 * @property \Spiral\ODM\ODM                    $odm
 * @property \Spiral\ORM\ORM                    $orm
 * @property \Spiral\Session\SessionStore       $session
 * @property \Spiral\Tokenizer\Tokenizer        $tokenizer
 * @property \Spiral\Translator\Translator      $i18n
 * @property \Spiral\Views\ViewManager          $view
 *
 * @property \Spiral\Redis\RedisManager         $redis
 * @property \Spiral\Image\ImageManager         $image
 */
class Core extends Container implements ConfiguratorInterface, HippocampusInterface, CoreInterface
{
    /**
     * Declares to IoC that component instance should be treated as singleton.
     */
    const SINGLETON = self::class;

    /**
     * Core version.
     */
    const VERSION = '0.9.0-alpha';

    /**
     * Bootstrap file name, if not redefined by application.
     */
    const BOOTSTRAP = 'bootstrap.php';

    /**
     * Runtime files and config extensions.
     */
    const EXTENSION = 'php';

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
        //Core interface bindings
        'Spiral\Core\ContainerInterface'        => 'Spiral\Core\Core',
        'Spiral\Core\ConfiguratorInterface'     => 'Spiral\Core\Core',
        'Spiral\Core\HippocampusInterface'      => 'Spiral\Core\Core',
        'Spiral\Core\CoreInterface'             => 'Spiral\Core\Core',

        //Instrumental bindings
        'Psr\Log\LoggerInterface'               => 'Spiral\Debug\Logger',
        'Spiral\Cache\StoreInterface'           => 'Spiral\Cache\CacheStore',
        'Spiral\Files\FilesInterface'           => 'Spiral\Files\FileManager',
        'Spiral\Views\ViewsInterface'           => 'Spiral\Views\ViewManager',
        'Spiral\Storage\StorageInterface'       => 'Spiral\Storage\StorageManager',
        'Spiral\Encrypter\EncrypterInterface'   => 'Spiral\Encrypter\Encrypter',
        'Spiral\Tokenizer\TokenizerInterface'   => 'Spiral\Tokenizer\Tokenizer',
        'Spiral\Translator\TranslatorInterface' => 'Spiral\Translator\Translator',
        'Spiral\Validation\ValidationInterface' => 'Spiral\Validation\Validator',

        //Spiral aliases
        'core'                                  => 'Spiral\Core\Core',
        'loader'                                => 'Spiral\Core\Loader',
        'modules'                               => 'Spiral\Modules\ModuleManager',

        //Dispatchers
        'console'                               => 'Spiral\Console\ConsoleDispatcher',
        'http' => 'Spiral\Http\HttpDispatcher',

        //Component aliases
        'cache'                                 => 'Spiral\Cache\CacheManager',
        'cookies'                               => 'Spiral\Http\Cookies\CookieManager',
        'dbal'                                  => 'Spiral\Database\DatabaseManager',
        'encrypter'                             => 'Spiral\Encrypter\Encrypter',
        'input'                                 => 'Spiral\Http\InputManager',
        'files'                                 => 'Spiral\Files\FileManager',
        'odm'                                   => 'Spiral\ODM\ODM',
        'orm'                                   => 'Spiral\ORM\ORM',
        'session'                               => 'Spiral\Session\SessionStore',
        'storage'                               => 'Spiral\Storage\StorageManager',
        'tokenizer'                             => 'Spiral\Tokenizer\Tokenizer',
        'i18n'                                  => 'Spiral\Translator\Translator',
        'view' => 'Spiral\Views\ViewManager',

        //Additional and post binded components
        'redis'                                 => 'Spiral\Redis\RedisManager',
        'image'                                 => 'Spiral\Image\ImageManager'
    ];

    /**
     * Current environment id (name), that value can be used directly in code by accessing
     * Core->getEnvironment() or Application->getEnvironment() (if you using that name), environment
     * can be changed at any moment via setEnvironment() method. Environment used to merge configuration
     * files (default + environment), so changing this value in a middle of application will keep
     * already initiated components binded to previous values.
     *
     * @var string
     */
    protected $environment = null;

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
     * Set of directory aliases defined during application bootstrap and in index.php file. Such
     * directory will be automatically resolved during reading config files and can be accessed using
     * Core->directory() method. You can redefine any directory at any moment of time using same method.
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
    protected $autoload = [Loader::class];//, ModuleManager::class];

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
        //Container constructing
        parent::__construct();

        $this->directories = $directories + [
                'config'  => $directories['application'] . '/config',
                'runtime' => $directories['application'] . '/runtime',
                'cache'   => $directories['application'] . '/runtime/cache'
            ];

        if (empty($this->environment))
        {
            /**
             * This is spiral shortcut to set environment, can be redefined by custom application
             * class.
             */
            $filename = $this->directory('runtime') . '/environment.php';
            $this->setEnvironment(file_exists($filename) ? (require $filename) : self::DEVELOPMENT);
        }

        /**
         * Timezones are really important.
         */
        date_default_timezone_set($this->timezone);
    }

    /**
     * Get core instance.
     *
     * @return static
     */
    public static function getInstance()
    {
        return self::getContainer()->get(static::class);
    }

    /**
     * Application enterpoint, should be called once in index.php file. That method will declare
     * runtime version of core, initiate loader and run bootstrap() method. Rest of application flow
     * will be controlled using Dispatcher instance which can be declared in $core->getDispatcher().
     * Use Application->start() to start application.
     *
     * @param array $directories Spiral directories should include root, libraries, config and runtime
     *                           directories.
     * @return static
     */
    public static function init(array $directories)
    {
        /**
         * @var Core $core
         */
        $core = new static($directories + ['framework' => dirname(__DIR__)]);

        $core->bindings = [
                static::class                => $core,
                self::class                  => $core,
                ContainerInterface::class    => $core,
                ConfiguratorInterface::class => $core,
                HippocampusInterface::class  => $core,
                CoreInterface::class         => $core,
            ] + $core->bindings;

        //Error and exception handlers
        set_error_handler([$core, 'handleError']);
        set_exception_handler([$core, 'handleException']);
        register_shutdown_function([$core, 'handleShutdown']);

        foreach ($core->autoload as $module)
        {
            $core->get($module);
        }

        //Bootstrapping
        $core->bootstrap();

        return $core;
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
     * Get all declared directory aliases.
     *
     * @return array
     */
    public function getDirectories()
    {
        return $this->directories;
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
     * Bootstrapping. Most of code responsible for routes, endpoints, events and other application
     * preparations should located in this method.
     */
    public function bootstrap()
    {
        if (file_exists($this->directory('application') . '/' . static::BOOTSTRAP))
        {
            //Old Fashion, btw there is very tasty cocktail under same name
            require($this->directory('application') . '/' . static::BOOTSTRAP);
        }
    }

    /**
     * Should return appropriate to use dispatcher, by default implementation core will select
     * dispatcher based on php environment, HTTP component will be used for web and CLI will be
     * constructed while calling from console. This method can be redefined to introduce new dispatchers
     * or logic to select one.
     *
     * @return DispatcherInterface
     */
    protected function createDispatcher()
    {
        $dispatcher = php_sapi_name() === 'cli'
            ? ConsoleDispatcher::class
            : HttpDispatcher::class;

        return $this->get($dispatcher);
    }

    /**
     * Start application processing by giving control to selected dispatcher.
     *
     * @param DispatcherInterface $dispatcher Forced application dispatcher.
     */
    public function start(DispatcherInterface $dispatcher = null)
    {
        $this->dispatcher = !empty($dispatcher) ? $dispatcher : $this->createDispatcher();
        $this->dispatcher->start();
    }

    /**
     * Configuration section to be loaded.
     *
     * @param string $section
     * @return array
     * @throws CoreException
     */
    public function getConfig($section = null)
    {
        $filename = $this->createFilename($section, $this->directories['config']);

        //Configuration cache ID
        $cached = str_replace(['/', '\\'], '-', 'config-' . $section);

        //Cached configuration
        if (empty($data = $this->loadData($cached, null, $cachedFilename)))
        {
            if (!file_exists($filename))
            {
                throw new CoreException(
                    "Unable to load '{$section}' configuration, file not found."
                );
            }

            $data = (require $filename);

            //Let's check for environment specific config
            $environment = $this->createFilename(
                $section,
                $this->directories['config'] . '/' . $this->environment
            );

            if (file_exists($environment))
            {
                $data = array_merge($data, (require $environment));
            }

            $this->saveData($cached, $data);

            return $data;
        }

        if (!file_exists($filename))
        {
            throw new CoreException("Unable to load '{$section}' configuration, file not found.");
        }

        if (filemtime($cachedFilename) < filemtime($filename))
        {
            //We can afford skipping FilesInterface here
            file_exists($cachedFilename) && unlink($cachedFilename);

            //Configuration were updated, reloading
            return $this->getConfig($section);
        }

        return $data;
    }

    /**
     * Read data from long memory cache. Will return null if no data presented.
     *
     * @param string $name
     * @param string $location Specific memory location.
     * @param string $filename Cache files.
     * @return mixed|array
     */
    public function loadData($name, $location = null, &$filename = null)
    {
        if (!file_exists($filename = $this->createFilename($name, $location)))
        {
            return null;
        }

        try
        {
            return (require $filename);
        }
        catch (\ErrorException $exception)
        {
            return null;
        }
    }

    /**
     * Put data to long memory cache.
     *
     * @param string $name
     * @param mixed  $data
     * @param string $location Specific memory location.
     */
    public function saveData($name, $data, $location = null)
    {
        //We need help to write file with directory creation
        $this->get(FilesInterface::class)->write(
            $this->createFilename($name, $location),
            '<?php return ' . var_export($data, true) . ';',
            FilesInterface::RUNTIME,
            true
        );
    }

    /**
     * Get extension to use for runtime data or configuration cache, all file in cache directory will
     * additionally get applicationID postfix.
     *
     * @param string $name     Runtime data file name (without extension).
     * @param string $location Location to store data in.
     * @return string
     */
    protected function createFilename($name, $location = null)
    {
        $name = str_replace(['/', '\\'], '-', $name);

        if (!empty($location))
        {
            return $location . '/' . $name . '.' . static::EXTENSION;
        }

        //Runtime cache
        return $this->directories['cache'] . "/$name-{$this->applicationID}" . '.' . static::EXTENSION;
    }

    /**
     * Call controller method by fully specified or short controller name, action and addition
     * options such as default controllers namespace, default name and postfix.
     *
     * Can be used for controller-less applications.
     *
     * @param string $controller Controller name, or class, or name with namespace prefix.
     * @param string $action     Controller action, empty by default (controller will use default action).
     * @param array  $parameters Additional methods parameters.
     * @return mixed
     * @throws ExceptionInterface
     */
    public function callAction($controller, $action = '', array $parameters = [])
    {
        if (!class_exists($controller))
        {
            throw new ControllerException(
                "No such controller '{$controller}' found.",
                ControllerException::NOT_FOUND
            );
        }

        //Initiating controller with all required dependencies
        $controller = $this->get($controller);
        if (!$controller instanceof ControllerInterface)
        {
            throw new ControllerException(
                "No such controller '{$controller}' found.",
                ControllerException::NOT_FOUND
            );
        }

        return $controller->callAction($this, $action, $parameters);
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
    public function handleError($code, $message, $filename = '', $line = 0)
    {
        ini_set('display_errors', false);
        throw new \ErrorException($message, $code, 0, $filename, $line);
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

        //GET SNAPSHOT

        //        if ($snapshot = $this->debug->handleException($exception))
        //        {
        //            if ($snapshot = $this->event('exception', $snapshot))
        //            {
        //                $this->dispatchSnapshot($snapshot);
        //            }
        //        }

        dumP($exception);
    }

    /**
     * Automatic handler for fatal and syntax error, this error can't be handled via default error
     * handler.
     */
    public function handleShutdown()
    {
        if ($error = error_get_last())
        {
            $this->handleError($error['type'], $error['message'], $error['file'], $error['line']);
        }
    }
}