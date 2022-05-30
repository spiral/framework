<?php

declare(strict_types=1);

namespace Spiral\Boot;

use Closure;
use Spiral\Boot\Bootloader\CoreBootloader;
use Spiral\Boot\BootloadManager\BootloadManager;
use Spiral\Boot\BootloadManager\Initializer;
use Spiral\Boot\Exception\BootException;
use Spiral\Core\Container;
use Spiral\Exceptions\ExceptionHandler;
use Spiral\Exceptions\ExceptionHandlerInterface;
use Spiral\Exceptions\ExceptionRendererInterface;
use Spiral\Exceptions\ExceptionReporterInterface;

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

    protected FinalizerInterface $finalizer;
    protected BootloadManager $bootloader;

    /** @var DispatcherInterface[] */
    protected array $dispatchers = [];

    /** @var array<Closure> */
    private array $bootingCallbacks = [];

    /** @var array<Closure> */
    private array $bootedCallbacks = [];

    /**
     * @throws \Throwable
     */
    protected function __construct(
        protected Container $container,
        protected ExceptionHandlerInterface $exceptionHandler,
        array $directories,
    ) {
        $container->bindSingleton(ExceptionHandlerInterface::class, $exceptionHandler);
        $container->bindSingleton(ExceptionRendererInterface::class, $exceptionHandler);
        $container->bindSingleton(ExceptionReporterInterface::class, $exceptionHandler);
        $container->bindSingleton(ExceptionHandler::class, $exceptionHandler);
        $container->bindSingleton(KernelInterface::class, $this);
        $container->bindSingleton(self::class, $this);
        $container->bindSingleton(static::class, $this);

        $container->bindSingleton(
            DirectoriesInterface::class,
            new Directories($this->mapDirectories($directories))
        );

        $this->finalizer = new Finalizer();
        $container->bindSingleton(FinalizerInterface::class, $this->finalizer);

        $this->bootloader = new BootloadManager($container, new Initializer($this->container));
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
     * Create an application instance.
     *
     * @param class-string<ExceptionHandlerInterface>|ExceptionHandlerInterface $exceptionHandler
     *
     * @throws \Throwable
     */
    public static function create(
        array $directories,
        bool $handleErrors = true,
        ExceptionHandlerInterface|string|null $exceptionHandler = null,
        Container $container = new Container(),
    ): static {
        $exceptionHandler ??= ExceptionHandler::class;

        if (\is_string($exceptionHandler)) {
            $exceptionHandler = $container->make($exceptionHandler);
        }
        if ($handleErrors) {
            $exceptionHandler->register();
        }

        return new static($container, $exceptionHandler, $directories);
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
        $environment ??= new Environment();
        $this->container->bindSingleton(EnvironmentInterface::class, $environment);

        try {
            // will protect any against env overwrite action
            $this->container->runScope(
                [EnvironmentInterface::class => $environment],
                function (): void {
                    $this->bootload();
                    $this->bootstrap();
                }
            );
        } catch (\Throwable $e) {
            $this->exceptionHandler->handleGlobalException($e);

            return null;
        }

        return $this;
    }

    /**
     * Register a new callback, that will be fired before framework bootloaders boot.
     * (Before all framework bootloaders will be booted)
     *
     * $kernel->booting(static function(KernelInterface $kernel) {
     *     $kernel->getContainer()->...
     * });
     *
     * @internal
     */
    public function booting(Closure ...$callbacks): void
    {
        foreach ($callbacks as $callback) {
            $this->bootingCallbacks[] = $callback;
        }
    }

    /**
     * Register a new callback, that will be fired after framework bootloaders booted.
     * (After booting all framework bootloaders)
     *
     * $kernel->booted(static function(KernelInterface $kernel) {
     *     $kernel->getContainer()->...
     * });
     *
     * @internal
     */
    public function booted(Closure ...$callbacks): void
    {
        foreach ($callbacks as $callback) {
            $this->bootedCallbacks[] = $callback;
        }
    }

    /**
     * Add new dispatcher. This method must only be called before method `serve`
     * will be invoked.
     */
    public function addDispatcher(DispatcherInterface $dispatcher): self
    {
        $this->dispatchers[] = $dispatcher;

        return $this;
    }

    /**
     * Start application and serve user requests using selected dispatcher or throw
     * an exception.
     *
     * @throws BootException
     * @throws \Throwable
     */
    public function serve(): mixed
    {
        foreach ($this->dispatchers as $dispatcher) {
            if ($dispatcher->canServe()) {
                return $this->container->runScope(
                    [DispatcherInterface::class => $dispatcher],
                    static fn () => $dispatcher->serve()
                );
            }
        }

        throw new BootException('Unable to locate active dispatcher.');
    }

    /**
     * Bootstrap application. Must be executed before serve method.
     */
    abstract protected function bootstrap(): void;

    /**
     * Normalizes directory list and adds all required aliases.
     */
    abstract protected function mapDirectories(array $directories): array;

    /**
     * Get list of defined kernel bootloaders
     *
     * @return array<int, class-string>|array<class-string, array<non-empty-string, mixed>>
     */
    protected function defineBootloaders(): array
    {
        return static::LOAD;
    }

    /**
     * Call the registered booting callbacks.
     */
    protected function fireCallbacks(array &$callbacks): void
    {
        if ($callbacks === []) {
            return;
        }

        do {
            $this->container->invoke(\current($callbacks));
        } while (\next($callbacks));

        $callbacks = [];
    }

    /**
     * Bootload all registered classes using BootloadManager.
     */
    private function bootload(): void
    {
        $self = $this;
        $this->bootloader->bootload(
            $this->defineBootloaders(),
            [
                static function () use ($self): void {
                    $self->fireCallbacks($self->bootingCallbacks);
                },
            ]
        );

        $this->fireCallbacks($this->bootedCallbacks);
    }
}
