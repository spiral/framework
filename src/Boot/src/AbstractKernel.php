<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Boot;

use Closure;
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

    /** @var array<Closure> */
    private $startingCallbacks = [];

    /** @var array<Closure> */
    private $startedCallbacks = [];

    /**
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
     * Create and initiate an application instance.
     *
     * @param array<string,string> $directories Directory map, "root" is required.
     * @param EnvironmentInterface|null $environment Application specific environment if any.
     * @param bool $handleErrors Enable global error handling.
     * @return self|static
     *
     * @throws \Throwable
     *
     * @deprecated since 3.0. Use Kernel::create(...)->run() instead.
     */
    public static function init(
        array $directories,
        EnvironmentInterface $environment = null,
        bool $handleErrors = true
    ): ?self {
        $core = self::create(
            $directories,
            $handleErrors
        );

        $core->container->bindSingleton(
            EnvironmentInterface::class,
            $environment ?? new Environment()
        );

        return $core->run($environment);
    }

    /**
     * Create an application instance.
     * @throws \Throwable
     */
    public static function create(
        array $directories,
        bool $handleErrors = true
    ): self {
        if ($handleErrors) {
            ExceptionHandler::register();
        }

        return new static(new Container(), $directories);
    }

    /**
     * Run the application with given Environment
     *
     * $app = App::create([...]);
     * $app->booting(...);
     * $app->booted(...);
     * $app->run(new Environment([
     *     'APP_ENV' => 'production'
     * ]));
     *
     */
    public function run(?EnvironmentInterface $environment = null): ?self
    {
        try {
            // will protect any against env overwrite action
            $this->container->runScope(
                [EnvironmentInterface::class => $environment ?? new Environment()],
                function (): void {
                    $this->bootload();
                    $this->bootstrap();
                }
            );
        } catch (\Throwable $e) {
            ExceptionHandler::handleException($e);

            return null;
        }

        return $this;
    }

    /**
     * Register a new callback, that will be fired before application start. (Before all bootloaders will be started)
     *
     * $kernel->starting(static function(KernelInterface $kernel) {
     *     $kernel->getContainer()->...
     * });
     *
     * @internal
     */
    public function starting(Closure ...$callbacks): void
    {
        foreach ($callbacks as $callback) {
            $this->startingCallbacks[] = $callback;
        }
    }

    /**
     * Register a new callback, that will be fired after application started. (After starting all bootloaders)
     *
     * $kernel->started(static function(KernelInterface $kernel) {
     *     $kernel->getContainer()->...
     * });
     *
     * @internal
     */
    public function started(Closure ...$callbacks): void
    {
        foreach ($callbacks as $callback) {
            $this->startedCallbacks[] = $callback;
        }
    }

    /**
     * Add new dispatcher. This method must only be called before method `serve`
     * will be invoked.
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
     * Bootstrap application. Must be executed before serve method.
     */
    abstract protected function bootstrap();

    /**
     * Normalizes directory list and adds all required aliases.
     */
    abstract protected function mapDirectories(array $directories): array;

    /**
     * Bootload all registered classes using BootloadManager.
     */
    private function bootload(): void
    {
        $self = $this;
        $this->bootloader->bootload(
            static::LOAD,
            [
                static function () use ($self): void {
                    $self->fireCallbacks($self->startingCallbacks);
                },
            ]
        );

        $this->fireCallbacks($this->startedCallbacks);
    }

    /**
     * Call the registered booting callbacks.
     */
    private function fireCallbacks(array &$callbacks): void
    {
        if ($callbacks === []) {
            return;
        }

        do {
            \current($callbacks)($this);
        } while (\next($callbacks));

        $callbacks = [];
    }
}
