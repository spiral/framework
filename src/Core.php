<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Framework;

use Spiral\Core\BootloadManager;
use Spiral\Core\Container;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Core\ContainerScope;
use Spiral\Framework\Bootloaders\CoreBootloader;
use Spiral\Framework\Exceptions\FrameworkException;

/**
 * Core responsible for application initialization, bootloading of all required services, environment and directory
 * management, exception handling.
 */
abstract class Core implements SingletonInterface
{
    /**
     * Defines list of bootloaders to be used for core initialisation and all system components.
     */
    protected const SYSTEM = [CoreBootloader::class];

    /**
     * List of bootloaders to be called on application initialization (before `serve` method).
     * This constant must be redefined in child application.
     */
    protected const LOAD = [];

    /** @var Container */
    protected $container;

    /** @var BootloadManager */
    protected $bootloader;

    /** @var DispatcherInterface[] */
    private $dispatchers = [];

    /**
     * @param Container $container
     * @param array     $directories
     */
    public function __construct(Container $container, array $directories)
    {
        $this->container = $container;

        $this->container->bindSingleton(self::class, $this);
        $this->container->bindSingleton(static::class, $this);

        $this->container->bindSingleton(
            DirectoriesInterface::class,
            new Directories($this->mapDirectories($directories))
        );

        $this->bootloader = new BootloadManager($this->container);
        $this->bootloader->bootload(static::SYSTEM);
    }

    /**
     * Add new dispatcher. This method must only be called before method `serve`
     * will be invoked.
     *
     * @param DispatcherInterface $dispatcher
     */
    public function addDispatcher(DispatcherInterface $dispatcher)
    {
        $this->dispatchers[] = $dispatcher;
    }

    /**
     * Start application and serve user requests using selected dispatcher or throw
     * an exception.
     *
     * @throws FrameworkException
     */
    public function serve()
    {
        foreach ($this->dispatchers as $dispatcher) {
            if ($dispatcher->canServe()) {
                $dispatcher->serve();
                return;
            }
        }

        throw new FrameworkException("Unable to locate active dispatcher.");
    }

    /**
     * Bootstrap application. Must be executed before start method.
     */
    abstract protected function bootstrap();

    /**
     * Normalizes directory list and adds all required alises.
     *
     * @param array $directories
     * @return array
     */
    protected function mapDirectories(array $directories): array
    {
        if (!isset($directories['root'])) {
            throw new FrameworkException("Missing required directory `root`.");
        }

        if (!isset($directories['app'])) {
            $directories['app'] = $directories['root'] . '/app/';
        }

        return array_merge($directories, [
            // public root
            'public'    => $directories['root'] . '/public/',

            // application directories
            'config'    => $directories['app'] . '/config/',
            'resources' => $directories['app'] . '/resources/',

            // data directories
            'runtime'   => $directories['app'] . '/runtime/',
            'cache'     => $directories['app'] . '/runtime/cache/',
        ]);
    }

    /**
     * Bootload all registered classes using BootloadManager.
     *
     * @return self
     */
    private function bootload(): self
    {
        $this->bootloader->bootload(static::LOAD);
        return $this;
    }

    /**
     * Initiate application core.
     *
     * @param array                $directories  Spiral directories should include root, libraries and application
     *                                           directories.
     * @param EnvironmentInterface $environment  Application specific environment if any.
     * @param bool                 $handleErrors Enable global error handling.
     * @return self
     */
    public static function init(
        array $directories,
        EnvironmentInterface $environment = null,
        bool $handleErrors = true
    ): self {
        if ($handleErrors) {
            ExceptionHandler::register();
        }

        $core = new static(new Container(), $directories);

        if (!empty($environment)) {
            $core->container->bind(EnvironmentInterface::class, $environment);
        }

        try {
            ContainerScope::runScope($core->container, function () use ($core) {
                $core->bootload()->bootstrap();
            });
        } catch (\Throwable $e) {
            ExceptionHandler::handleException($e);
        }

        return $core;
    }
}