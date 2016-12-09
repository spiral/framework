<?php

/**
 * spiral
 *
 * @author    Wolfy-J
 */
namespace Spiral\Core;

use Interop\Container\ContainerInterface as InteropContainer;
use Spiral\Core\Containers\SpiralContainer;
use Spiral\Core\Exceptions\CoreException;
use Spiral\Core\Exceptions\DirectoryException;
use Spiral\Core\Exceptions\ScopeException;
use Spiral\Debug\Traits\BenchmarkTrait;
use Spiral\Files\FilesInterface;

abstract class Core extends Component implements DirectoriesInterface
{
    use BenchmarkTrait;

    /**
     * I need this constant for Symfony Console. :/
     */
    const VERSION = '0.9.0-beta';

    /**
     * Memory section for bootloaders cache.
     */
    const BOOT_MEMORY = 'app';

    /**
     * Components to be autoloader while application initialization. This property can be redefined
     * on application level.
     */
    const BOOTLOAD = [];

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
     * @var EnvironmentInterface
     */
    private $environment = null;

    /**
     * @var BootloadManager
     */
    private $bootloader = null;

    /**
     * @var ContainerInterface
     */
    protected $container = null;

    /**
     * Application memory.
     *
     * @var MemoryInterface
     */
    protected $memory = null;

    /**
     * Components to be autoloader while application initialization. This property can be redefined
     * on application level.
     */
    protected $bootload = [];

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

        if (empty($memory)) {
            //Default memory implementation
            $memory = new Memory(
                $this->directory('cache'),
                $container->get(FilesInterface::class)
            );
        }

        $this->memory = $memory;
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
        } catch (\Error $exception) {
            throw new CoreException($exception->getMessage(), $exception->getCode(), $exception);
        }

        $this->timezone = $timezone;

        return $this;
    }

    /**
     * Get active application timezone.
     *
     * @return \DateTimeZone
     */
    public function timezone(): \DateTimeZone
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
    public function environment()
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
    public function bootloader()
    {
        return $this->bootloader;
    }

    //----

    public function start()
    {

    }

    //---

    /**
     * Bootstrap application. Must be executed before start method.
     */
    abstract protected function bootstrap();

    /**
     * Bootload all registered classes using BootloadManager.
     *
     * @return $this
     */
    private function bootload()
    {
        $this->bootloader->bootload(
            $this->bootload + static::BOOTLOAD,
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
        if (empty(self::staticContainer())) {
            throw new ScopeException("No shared/global container scope are set");
        }

        return self::staticContainer();
    }

    /**
     * Initiate application core. Method will set global container if none exists.
     *
     * @param array                $directories Spiral directories should include root, libraries
     *                                          and application directories.
     * @param ContainerInterface   $container   Initial container instance.
     * @param bool                 $handleErrors
     * @param EnvironmentInterface $environment Application specific environment if any.
     *
     * @return self
     */
    public static function init(
        array $directories,
        ContainerInterface $container = null,
        bool $handleErrors = true,
        EnvironmentInterface $environment = null
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
        //$container->bindSingleton(CoreInterface::class, $core);
        $container->bindSingleton(DirectoriesInterface::class, $core);

        //Core shared components
        //$container->bindSingleton(BootloadManager::class, $core->bootloader);
        $container->bindSingleton(MemoryInterface::class, $core->memory);

        //Setting environment (by default - dotenv extension)
        if (empty($environment)) {
            $environment = new Environment(
                $core->directory('root') . '.env',
                $container->get(FilesInterface::class),
                $core->memory
            );

            //Need way to redefine environment
            $environment->load();
        }

        //Mounting environment to be available for other components
        $core->setEnvironment($environment);

        //Initiating config loader
        $container->bindSingleton(
            ConfiguratorInterface::class,
            $container->make(Configurator::class, ['directory' => $core->directory('config')])
        );

        //Error and exception handlers
        if ($handleErrors) {
//            register_shutdown_function([$core, 'handleShutdown']);
//            set_error_handler([$core, 'handleError']);
//            set_exception_handler([$core, 'handleException']);
        }

        $outerContainer = self::staticContainer($container);
        try {
            //Bootloading our application in a defined GLOBAL container scope
            $core->bootload()->bootstrap();
        } finally {
            self::staticContainer($outerContainer);
        }

        return $core;
    }
}