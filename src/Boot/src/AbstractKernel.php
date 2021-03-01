<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Boot;

use Spiral\Boot\Bootloader\CoreBootloader;
use Spiral\Boot\Exception\BootException;
use Spiral\Core\Container;

/**
 * Core responsible for application initialization, bootloading of all required services,
 * environment and directory management, exception handling.
 */
abstract class AbstractKernel implements KernelInterface
{
    /** Defines list of bootloaders to be used for core initialisation and all system components. */
    protected const SYSTEM = [CoreBootloader::class];

    /**
     * List of bootloaders to be called on application initialization (before `serve` method).
     * This constant must be redefined in child application.
     */
    protected const LOAD = [];

    /** @var Container */
    protected $container;

    /** @var FinalizerInterface */
    protected $finalizer;

    /** @var BootloadManager */
    protected $bootloader;

    /** @var DispatcherInterface[] */
    protected $dispatchers = [];

    /**
     * @param Container $container
     * @param array     $directories
     *
     * @throws \Throwable
     */
    public function __construct(Container $container, array $directories)
    {
        $this->container = $container;

        $this->container->bindSingleton(KernelInterface::class, $this);
        $this->container->bindSingleton(self::class, $this);
        $this->container->bindSingleton(static::class, $this);

        $this->container->bindSingleton(
            DirectoriesInterface::class,
            new Directories($this->mapDirectories($directories))
        );

        $this->finalizer = new Finalizer();
        $this->container->bindSingleton(FinalizerInterface::class, $this->finalizer);

        $this->bootloader = new BootloadManager($this->container);
        $this->bootloader->bootload(static::SYSTEM);
    }

    /**
     * Terminate the application.
     */
    public function __destruct()
    {
        $this->finalizer->finalize(true);
    }

    /**
     * Add new dispatcher. This method must only be called before method `serve`
     * will be invoked.
     *
     * @param DispatcherInterface $dispatcher
     */
    public function addDispatcher(DispatcherInterface $dispatcher): void
    {
        $this->dispatchers[] = $dispatcher;
    }

    /**
     * Start application and serve user requests using selected dispatcher or throw
     * an exception.
     *
     * @return mixed
     * @throws BootException
     * @throws \Throwable
     */
    public function serve()
    {
        foreach ($this->dispatchers as $dispatcher) {
            if ($dispatcher->canServe()) {
                return $this->container->runScope(
                    [DispatcherInterface::class => $dispatcher],
                    [$dispatcher, 'serve']
                );
            }
        }

        throw new BootException('Unable to locate active dispatcher.');
    }

    /**
     * Initiate application core.
     *
     * @param array                     $directories Directory map, "root" is required.
     * @param EnvironmentInterface|null $environment Application specific environment if any.
     * @param bool                      $handleErrors Enable global error handling.
     * @return self|static
     *
     * @throws \Throwable
     */
    public static function init(
        array $directories,
        EnvironmentInterface $environment = null,
        bool $handleErrors = true
    ): ?self {
        if ($handleErrors) {
            ExceptionHandler::register();
        }

        $environment = $environment ?? new Environment();

        $core = new static(new Container(), $directories);
        $core->container->bindSingleton(EnvironmentInterface::class, $environment);

        try {
            // will protect any against env overwrite action
            $core->container->runScope(
                [EnvironmentInterface::class => $environment],
                function () use ($core): void {
                    $core->bootload();
                    $core->bootstrap();
                }
            );
        } catch (\Throwable $e) {
            ExceptionHandler::handleException($e);

            return null;
        }

        return $core;
    }

    /**
     * Bootstrap application. Must be executed before serve method.
     */
    abstract protected function bootstrap();

    /**
     * Normalizes directory list and adds all required aliases.
     *
     * @param array $directories
     * @return array
     */
    abstract protected function mapDirectories(array $directories): array;

    /**
     * Bootload all registered classes using BootloadManager.
     */
    private function bootload(): void
    {
        $this->bootloader->bootload(static::LOAD);
    }
}
