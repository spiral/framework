<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Core;

use Interop\Container\ContainerInterface as InteropContainer;
use Spiral\Console\ConsoleDispatcher;
use Spiral\Core\Containers\SpiralContainer;
use Spiral\Core\Exceptions\ControllerException;
use Spiral\Core\Exceptions\CoreException;
use Spiral\Core\Exceptions\FatalException;
use Spiral\Core\Exceptions\SugarException;
use Spiral\Core\HMVC\ControllerInterface;
use Spiral\Core\HMVC\CoreInterface;
use Spiral\Core\Traits\SharedTrait;
use Spiral\Debug\SnapshotInterface;
use Spiral\Debug\Traits\BenchmarkTrait;
use Spiral\Files\FilesInterface;
use Spiral\Http\HttpDispatcher;

/**
 * Spiral core responsible for application timezone, memory, represents spiral container (can be
 * overwritten with custom instance).
 *
 * @property-read ContainerInterface $container Protected.
 * @todo move start method and dispatcher property into trait
 * @todo potentially add more events and create common event dispatcher?
 */
abstract class Core extends Component implements CoreInterface, DirectoriesInterface
{
    /**
     * Simplified access to container bindings.
     */
    use SharedTrait, BenchmarkTrait;

    /**
     * Set to false if you don't want spiral to cache autoloading list.
     */
    const MEMORIZE_BOOTLOADERS = true;

    /**
     * I need this constant for Symfony Console. :/
     */
    const VERSION = '0.8.0-beta';

    /**
     * Every application should have defined timezone.
     *
     * @see setTimezone()
     * @see timezone()
     * @var string
     */
    private $timezone = 'UTC';

    /**
     * Set of primary application directories.
     *
     * @see setDirectory()
     * @see directory()
     * @see getDirectories()
     * @var array
     */
    private $directories = [
        'root'        => null,
        'public'      => null,
        'libraries'   => null,
        'framework'   => null,
        'application' => null,
        'locales'     => null,
        'runtime'     => null,
        'config'      => null,
        'cache'       => null
    ];

    /**
     * @var BootloadManager
     */
    private $bootloader = null;

    /**
     * @var EnvironmentInterface
     */
    private $environment = null;

    /**
     * Application memory.
     *
     * @whatif private
     * @var HippocampusInterface
     */
    protected $memory = null;

    /**
     * Not set until start method. Can be set manually in bootload.
     *
     * @whatif private
     * @var DispatcherInterface|null
     */
    protected $dispatcher = null;

    /**
     * Components to be autoloader while application initialization.
     *
     * @var array
     */
    protected $load = [];

    /**
     * Core class will extend default spiral container and initiate set of directories. You must
     * provide application, libraries and root directories to constructor.
     *
     * @param array                $directories Core directories list. Every directory must have /
     *                                          at the end.
     * @param ContainerInterface   $container
     * @param HippocampusInterface $memory
     */
    public function __construct(
        array $directories,
        ContainerInterface $container,
        HippocampusInterface $memory = null
    ) {
        $this->container = $container;

        /*
         * Default directories pattern, you can overwrite any directory you want in index file.
         */
        $this->directories = $directories + [
                'framework' => dirname(__DIR__) . '/',
                'public'    => $directories['root'] . 'webroot/',
                'config'    => $directories['application'] . 'config/',
                'views'     => $directories['application'] . 'views/',
                'runtime'   => $directories['application'] . 'runtime/',
                'cache'     => $directories['application'] . 'runtime/cache/',
                'resources' => $directories['application'] . 'resources/',
                'locales'   => $directories['application'] . 'resources/locales/'
            ];

        //Every application needs timezone to be set, by default we are using UTC
        date_default_timezone_set($this->timezone);

        if (empty($memory)) {
            //Default memory implementation
            $memory = new Memory($this->directory('cache'), $container->get(FilesInterface::class));
        }

        $this->memory = $memory;
        $this->bootloader = new BootloadManager($this->container, $this->memory);
    }

    /**
     * Set application environment.
     *
     * @param EnvironmentInterface $environment
     */
    public function setEnvironment(EnvironmentInterface $environment)
    {
        $this->environment = $environment;
    }

    /**
     * @return EnvironmentInterface
     * @throws CoreException
     */
    public function environment()
    {
        if (empty($this->environment)) {
            throw new CoreException("Application environment not set.");
        }

        return $this->environment;
    }

    /**
     * @return BootloadManager
     */
    public function bootloader()
    {
        return $this->bootloader;
    }

    /**
     * Change application timezone.
     *
     * @param string $timezone
     * @return $this
     * @throws CoreException
     */
    public function setTimezone($timezone)
    {
        try {
            date_default_timezone_set($timezone);
        } catch (\Exception $exception) {
            throw new CoreException($exception->getMessage(), $exception->getCode(), $exception);
        }

        $this->timezone = $timezone;

        return $this;
    }

    /**
     * Get active application timezone.
     *
     * @return string
     */
    public function timezone()
    {
        return $this->timezone;
    }

    /**
     * Set application directory.
     *
     * @param string $alias Directory alias, ie. "framework".
     * @param string $path  Directory path without ending slash.
     * @return $this
     */
    public function setDirectory($alias, $path)
    {
        $this->directories[$alias] = rtrim($path, '/\\') . '/';

        return $this;
    }

    /**
     * Get application directory.
     *
     * @param string $alias
     * @return string
     */
    public function directory($alias)
    {
        return $this->directories[$alias];
    }

    /**
     * All application directories.
     *
     * @return array
     */
    public function getDirectories()
    {
        return $this->directories;
    }

    /**
     * {@inheritdoc}
     */
    public function callAction($controller, $action = '', array $parameters = [])
    {
        if (!class_exists($controller)) {
            throw new ControllerException(
                "No such controller '{$controller}' found.",
                ControllerException::NOT_FOUND
            );
        }

        $benchmark = $this->benchmark('callAction', $controller . '::' . ($action ?: '~default~'));
        try {
            //Initiating controller with all required dependencies
            $controller = $this->container->make($controller);

            if (!$controller instanceof ControllerInterface) {
                throw new ControllerException(
                    "No such controller '{$controller}' found.",
                    ControllerException::NOT_FOUND
                );
            }


            return $controller->callAction($action, $parameters);
        } finally {
            $this->benchmark($benchmark);
        }
    }

    /**
     * Start application using custom or default dispatcher.
     *
     * @param DispatcherInterface $dispatcher Custom dispatcher.
     */
    public function start(DispatcherInterface $dispatcher = null)
    {
        $this->dispatcher = !empty($dispatcher) ? $dispatcher : $this->createDispatcher();
        $this->dispatcher->start();
    }

    /**
     * Bootstrap application. Must be executed before start method.
     */
    abstract protected function bootstrap();

    /**
     * Handle php shutdown and search for fatal errors.
     */
    public function handleShutdown()
    {
        if (!empty($error = error_get_last())) {
            $this->handleException(new FatalException(
                $error['message'], $error['type'], 0, $error['file'], $error['line']
            ));
        }
    }

    /**
     * Convert application error into exception.
     *
     * @param int    $code
     * @param string $message
     * @param string $filename
     * @param int    $line
     * @throws \ErrorException
     */
    public function handleError($code, $message, $filename = '', $line = 0)
    {
        throw new \ErrorException($message, $code, 0, $filename, $line);
    }

    /**
     * Handle exception using associated application dispatcher and snapshot class.
     *
     * @param \Exception $exception Works well in PHP7.
     */
    public function handleException($exception)
    {
        restore_error_handler();
        restore_exception_handler();

        if (empty($snapshot = $this->getSnapshot($exception))) {
            //No action is required
            return;
        }

        //Let's allow snapshot to report about itself
        $snapshot->report();

        if (!empty($this->dispatcher)) {
            //Now dispatcher can handle snapshot it's own way
            $this->dispatcher->handleSnapshot($snapshot);
        } else {
            echo $snapshot;
        }
    }

    /**
     * Create appropriate snapshot for given exception. By default SnapshotInterface binding will be
     * used.
     *
     * Method can return null, in this case exception will be ignored.
     *
     * @param \Throwable $exception
     * @return SnapshotInterface|null
     */
    public function getSnapshot($exception)
    {
        if (!$this->container->has(SnapshotInterface::class)) {
            return null;
        }

        return $this->container->make(
            SnapshotInterface::class,
            compact('exception')
        );
    }

    /**
     * Create default application dispatcher based on environment value.
     *
     * @todo possibly split into two protected methods to let user define dispatcher easier
     * @return DispatcherInterface|ConsoleDispatcher|HttpDispatcher
     */
    protected function createDispatcher()
    {
        if (php_sapi_name() === 'cli') {
            return $this->container->make(ConsoleDispatcher::class);
        }

        return $this->container->make(HttpDispatcher::class);
    }

    /**
     * Shared container instance (needed for helpers and etc).
     *
     * @return InteropContainer
     */
    public static function sharedContainer()
    {
        if (empty(self::staticContainer())) {
            throw new SugarException("No shared/static container are set.");
        }

        return self::staticContainer();
    }

    /**
     * Initiate application core. Method will set global container if none exists.
     *
     * @param array              $directories Spiral directories should include root, libraries and
     *                                        application directories.
     * @param ContainerInterface $container   Initial container instance.
     * @param bool               $handleErrors
     * @return static
     */
    public static function init(
        array $directories,
        ContainerInterface $container = null,
        $handleErrors = true
    ) {
        if (empty($container)) {
            //Default spiral container
            $container = new SpiralContainer();
        }

        //Spiral core interface, @see SpiralContainer
        $container->bindSingleton(ContainerInterface::class, $container);

        //Some sugar for modules, technically can be used as wrapper only here and in start method
        if (empty(self::staticContainer())) {
            //todo: better logic is required, stack wrapping?
            self::staticContainer($container);
        }

        /**
         * @var Core $core
         */
        $core = new static($directories, $container);

        //Core binding
        $container->bindSingleton(self::class, $core);
        $container->bindSingleton(static::class, $core);
        $container->bindSingleton(DirectoriesInterface::class, $core);
        $container->bindSingleton(BootloadManager::class, $core->bootloader);
        $container->bindSingleton(HippocampusInterface::class, $core->memory);
        $container->bindSingleton(CoreInterface::class, $core);

        $container->bindSingleton(ConfiguratorInterface::class, $container->make(
            Configurator::class, ['directory' => $core->directory('config')]
        ));

        //Setting environment (by default - dotenv extension)
        $core->environment = new Environment(
            $core->directory('root') . '.env',
            $container->get(FilesInterface::class),
            $core->memory
        );

        $core->environment->load();

        $container->bindSingleton(EnvironmentInterface::class, $core->environment);

        //Error and exception handlers
        if ($handleErrors) {
            register_shutdown_function([$core, 'handleShutdown']);
            set_error_handler([$core, 'handleError']);
            set_exception_handler([$core, 'handleException']);
        }

        $core->bootload()->bootstrap();

        return $core;
    }

    /**
     * Bootload all registered classes using BootloadManager.
     *
     * @return $this
     */
    private function bootload()
    {
        //Bootloading all needed components and extensions
        $this->bootloader->bootload(
            $this->load,
            static::MEMORIZE_BOOTLOADERS ? 'bootloading' : null
        );

        return $this;
    }
}