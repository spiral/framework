<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Core;

use Interop\Container\ContainerInterface as InteropContainer;
use Spiral\Console\ConsoleDispatcher;
use Spiral\Core\Containers\SpiralContainer;
use Spiral\Core\Exceptions\CoreException;
use Spiral\Core\Exceptions\DirectoryException;
use Spiral\Core\Exceptions\FatalException;
use Spiral\Core\Exceptions\ScopeException;
use Spiral\Core\HMVC\CoreInterface;
use Spiral\Core\Traits\SharedTrait;
use Spiral\Debug\SnapshotInterface;
use Spiral\Files\FilesInterface;
use Spiral\Http\HttpDispatcher;

/**
 * Spiral core responsible for application timezone, memory, represents spiral container (can be
 * overwritten with custom instance).
 *
 * Btw, you can design your architecture any way you want: MVC, MMVC, HMVC, ADR, anything which can
 * be invoked and/or routed. Technically you can even invent your own, application specific,
 * architecture.
 */
abstract class Core extends AbstractCore implements DirectoriesInterface
{
    use SharedTrait;

    /**
     * I need this constant for Symfony Console. :/
     */
    const VERSION = '0.9.0-rc';

    /**
     * Memory section for bootloaders cache.
     */
    const BOOT_MEMORY = 'app';

    /**
     * Components to be autoloader while application initialization. This property can be redefined
     * on application level.
     */
    const LOAD = [];

    /**
     * Every application should have defined timezone.
     *
     * @see setTimezone()
     * @see getTimezone()
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
    protected $bootloader;

    /**
     * @var EnvironmentInterface
     */
    protected $environment;

    /**
     * Not set until start method. Can be set manually in bootload.
     *
     * @var DispatcherInterface
     */
    protected $dispatcher;

    /**
     * Application memory.
     *
     * @invisible
     * @var MemoryInterface
     */
    protected $memory;

    /**
     * Components to be autoloader while application initialization. This property can be redefined
     * on application level.
     *
     * @deprecated use LOAD constant instead
     * @invisible
     */
    protected $load = [];

    /**
     * Core class will extend default spiral container and initiate set of directories. You must
     * provide application, libraries and root directories to constructor.
     *
     * @param array              $directories   Core directories list. Every directory must have /
     *                                          at the end.
     * @param ContainerInterface $container
     * @param MemoryInterface    $memory
     */
    public function __construct(
        array $directories,
        ContainerInterface $container,
        MemoryInterface $memory = null
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

        //Default memory implementation as fallback
        $this->memory = $memory ?? new Memory(
                $this->directory('cache'),
                $container->get(FilesInterface::class)
            );

        $this->bootloader = new BootloadManager($this->container, $this->memory);
    }

    /**
     * Change application timezone.
     *
     * @param string $timezone
     *
     * @return $this|self
     * @throws CoreException
     */
    public function setTimezone(string $timezone): Core
    {
        try {
            date_default_timezone_set($timezone);
        } catch (\Exception $e) {
            throw new CoreException($e->getMessage(), $e->getCode(), $e);
        }

        $this->timezone = $timezone;

        return $this;
    }

    /**
     * Get active application timezone.
     *
     * @return \DateTimeZone
     */
    public function getTimezone(): \DateTimeZone
    {
        return new \DateTimeZone($this->timezone);
    }

    /**
     * {@inheritdoc}
     */
    public function hasDirectory(string $alias): bool
    {
        return isset($this->directories[$alias]);
    }

    /**
     * {@inheritdoc}
     */
    public function setDirectory(string $alias, string $path): DirectoriesInterface
    {
        $this->directories[$alias] = rtrim($path, '/\\') . '/';

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function directory(string $alias): string
    {
        if (!$this->hasDirectory($alias)) {
            throw new DirectoryException("Undefined directory alias '{$alias}'");
        }

        return $this->directories[$alias];
    }

    /**
     * {@inheritdoc}
     */
    public function getDirectories(): array
    {
        return $this->directories;
    }

    /**
     * Change application environment. Attention, already loaded configs would not be altered!
     *
     * @param EnvironmentInterface $environment
     */
    public function setEnvironment(EnvironmentInterface $environment)
    {
        $this->environment = $environment;

        //Making sure environment is available in container scope
        $this->container->bindSingleton(EnvironmentInterface::class, $this->environment);
    }

    /**
     * @return EnvironmentInterface
     *
     * @throws CoreException
     */
    public function getEnvironment()
    {
        if (empty($this->environment)) {
            throw new CoreException("Application environment not set");
        }

        return $this->environment;
    }

    /**
     * BootloadManager responsible for initiation of your application.
     *
     * @return BootloadManager
     */
    public function getBootloader()
    {
        return $this->bootloader;
    }

    /**
     * Handle php shutdown and search for fatal errors.
     */
    public function handleShutdown()
    {
        if (!$this->container->has(SnapshotInterface::class)) {
            //We are unable to handle exception without proper snaphotter
            return;
        }

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
     *
     * @throws \ErrorException
     */
    public function handleError($code, $message, $filename = '', $line = 0)
    {
        throw new \ErrorException($message, $code, 0, $filename, $line);
    }

    /**
     * Handle exception using associated application dispatcher and snapshot class.
     *
     * @param \Throwable $exception
     *
     * @throws \Throwable
     */
    public function handleException(\Throwable $exception)
    {
        restore_error_handler();
        restore_exception_handler();

        $snapshot = $this->makeSnapshot($exception);

        if (empty($snapshot)) {
            //No action is required
            throw $exception;
        }

        //Let's allow snapshot to report about itself
        $snapshot->report();

        if (!empty($this->dispatcher)) {
            //Now dispatcher can handle snapshot it's own way
            $this->dispatcher->handleSnapshot($snapshot);
        } else {
            echo $snapshot->render();
        }
    }

    /**
     * Create appropriate snapshot for given exception. By default SnapshotInterface binding will be
     * used.
     *
     * Method can return null, in this case exception will be ignored and handled default way.
     *
     * @param \Throwable $exception
     *
     * @return SnapshotInterface|null
     */
    public function makeSnapshot(\Throwable $exception)
    {
        if (!$this->container->has(SnapshotInterface::class)) {
            return null;
        }

        return $this->container->make(SnapshotInterface::class, compact('exception'));
    }

    /**
     * Start application using custom or default dispatcher.
     *
     * @param DispatcherInterface $dispatcher Custom dispatcher.
     */
    public function start(DispatcherInterface $dispatcher = null)
    {
        $this->dispatcher = $dispatcher ?? $this->createDispatcher();
        $this->dispatcher->start();
    }

    /**
     * Bootstrap application. Must be executed before start method.
     */
    abstract protected function bootstrap();

    /**
     * Create default application dispatcher based on environment value.
     *
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
     * Bootload all registered classes using BootloadManager.
     *
     * @return $this
     */
    private function bootload()
    {
        $this->bootloader->bootload(
            $this->load + static::LOAD,
            $this->environment->get('CACHE_BOOTLOADERS', false) ? static::BOOT_MEMORY : null
        );

        return $this;
    }

    /**
     * Shared container instance (needed for helpers and etc). Attention, method will fail if no
     * global container is set.
     *
     * @return InteropContainer
     *
     * @throws ScopeException
     */
    public static function sharedContainer()
    {
        $container = self::staticContainer();
        if (empty($container)) {
            throw new ScopeException("No shared/global container scope are set");
        }

        return $container;
    }

    /**
     * Initiate application core. Method will set global container if none exists.
     *
     * @param array                $directories Spiral directories should include root, libraries
     *                                          and application directories.
     * @param EnvironmentInterface $environment Application specific environment if any.
     * @param ContainerInterface   $container   Initial container instance.
     * @param bool                 $handleErrors
     *
     * @return self
     */
    public static function init(
        array $directories,
        EnvironmentInterface $environment = null,
        ContainerInterface $container = null,
        bool $handleErrors = true
    ): self {
        //Default spiral container
        $container = $container ?? new SpiralContainer();

        //Spiral core interface, @see SpiralContainer
        $container->bindSingleton(ContainerInterface::class, $container);

        /**
         * @var Core $core
         */
        $core = new static($directories, $container);

        //Core binding
        $container->bindSingleton(self::class, $core);
        $container->bindSingleton(static::class, $core);

        //Core shared interfaces
        $container->bindSingleton(CoreInterface::class, $core);
        $container->bindSingleton(DirectoriesInterface::class, $core);

        //Core shared components
        $container->bindSingleton(BootloadManager::class, $core->bootloader);
        $container->bindSingleton(MemoryInterface::class, $core->memory);

        //Setting environment (by default - dotenv extension)
        if (empty($environment)) {
            /*
             * Default spiral environment is based on .env file.
             */
            $environment = new DotenvEnvironment(
                $core->directory('root') . '.env',
                $core->memory
            );
        }

        //Mounting environment to be available for other components
        $core->setEnvironment($environment);

        //Initiating config loader
        $container->bindSingleton(
            ConfiguratorInterface::class,
            $container->make(ConfigFactory::class, ['directory' => $core->directory('config')])
        );

        if ($handleErrors) {
            //Error and exception handlers
            register_shutdown_function([$core, 'handleShutdown']);
            set_error_handler([$core, 'handleError']);
            set_exception_handler([$core, 'handleException']);
        }

        $scope = self::staticContainer($container);
        try {
            //Bootloading our application in a defined GLOBAL container scope
            $core->bootload()->bootstrap();
        } finally {
            self::staticContainer($scope);
        }

        return $core;
    }
}