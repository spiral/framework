<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Core;

use Dotenv\Dotenv;
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
use Spiral\Files\FilesInterface;
use Spiral\Http\HttpDispatcher;

/**
 * Spiral core responsible for application timezone, memory, represents spiral container (can be
 * overwritten with custom instance).
 *
 * @property-read ContainerInterface $container Protected.
 *
 * @todo move start method and dispatcher property into trait
 */
class Core extends Component implements CoreInterface, DirectoriesInterface
{
    /**
     * Simplified access to container bindings.
     */
    use SharedTrait;

    /**
     * Set to false if you don't want spiral to cache autoloading list.
     */
    const MEMORIZE_BOOTLOADERS = true;

    /**
     * I need a constant for Symfony Console. :/
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
     * Application memory.
     *
     * @var HippocampusInterface
     */
    protected $memory = null;

    /**
     * Not set until start method. Can be set manually in bootload.
     *
     * @var DispatcherInterface|null
     */
    protected $dispatcher = null;

    /**
     * @var BootloadProcessor
     */
    protected $bootloader = null;

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

        //We can set some directories automatically
        $this->directories = $directories + [
                'framework' => dirname(__DIR__) . '/',
                'public'    => $directories['root'] . 'webroot/',
                'config'    => $directories['application'] . 'config/',
                'runtime'   => $directories['application'] . 'runtime/',
                'cache'     => $directories['application'] . 'runtime/cache/',
                'locales'   => $directories['application'] . 'locales/'
            ];

        if (empty($memory)) {
            //Default memory implementation
            $memory = new Memory($this->directory('cache'), $container->get(FilesInterface::class));
        }

        $this->memory = $memory;

        date_default_timezone_set($this->timezone);

        //Initial env variables
        $this->initEnvironment();
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

        //Initiating controller with all required dependencies
        $controller = $this->container->get($controller);

        if (!$controller instanceof ControllerInterface) {
            throw new ControllerException(
                "No such controller '{$controller}' found.",
                ControllerException::NOT_FOUND
            );
        }

        return $controller->callAction($action, $parameters);
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
    public function bootstrap()
    {
        //Doing nothing here
    }

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
     * @param \Exception $exception PHP7, are you ok?
     */
    public function handleException($exception)
    {


        restore_error_handler();
        restore_exception_handler();

        /**
         * @var SnapshotInterface $snapshot
         */
        $snapshot = $this->container->make(SnapshotInterface::class, compact('exception'));

        //Reporting
        $snapshot->report();

        if (!empty($this->dispatcher)) {
            //Now dispatcher can handle snapshot it's own way
            $this->dispatcher->handleSnapshot($snapshot);
        } else {
            echo $snapshot;
        }
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
     * Bootload all registered bootloader using BootloadProcessor.
     */
    protected function bootload()
    {
        //Bootloading all needed components and extensions
        $this->bootloader = new BootloadProcessor($this->load, $this->memory);

        if (static::MEMORIZE_BOOTLOADERS) {
            $this->bootloader->bootload($this->container, 'bootloading');
        } else {
            $this->bootloader->bootload($this->container);
        }
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

        /**
         * @var Core $spiral
         */
        $spiral = new static($directories, $container);

        //Self binding
        $container->bindSingleton(ContainerInterface::class, $container);

        //Core binding
        $container->bindSingleton(self::class, $spiral)->bind(static::class, $spiral);

        //Directories manager
        $container->bindSingleton(DirectoriesInterface::class, $spiral);

        //Memory binding
        $container->bindSingleton(HippocampusInterface::class, $spiral->memory);

        //HMVC core binding
        $container->bindSingleton(CoreInterface::class, $spiral);

        //Configurator is needed to configure every other component
        $configurator = $container->make(Configurator::class, [
            'directory' => $spiral->directory('config')
        ]);

        //Configurator binding
        $container->bindSingleton(ConfiguratorInterface::class, $configurator);

        //Error and exception handlers
        if ($handleErrors) {
            register_shutdown_function([$spiral, 'handleShutdown']);
            set_error_handler([$spiral, 'handleError']);
            set_exception_handler([$spiral, 'handleException']);
        }

        //Some sugar for modules, technically can be used as wrapper only here and in start method
        self::staticContainer($spiral->container);

        $spiral->bootload();

        //Bootstrapping our application
        $spiral->bootstrap();

        return $spiral;
    }

    /**
     * Define current environment using either application memory or .env file (slower).
     *
     * @todo moved into separate component and cached inside memory
     */
    private function initEnvironment()
    {
        if (!file_exists($this->directory('root') . '.env')) {
            return;
        }

        /**
         * DotEnv is pretty slow, i have to cache it using hippocampus at one moment.
         */
        $dotenv = new Dotenv($this->directory('root'));
        $dotenv->load();
    }
}