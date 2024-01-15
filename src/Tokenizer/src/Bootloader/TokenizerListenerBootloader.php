<?php

declare(strict_types=1);

namespace Spiral\Tokenizer\Bootloader;

use Spiral\Boot\AbstractKernel;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\Memory;
use Spiral\Bootloader\Attributes\AttributesBootloader;
use Spiral\Core\Attribute\Singleton;
use Spiral\Core\FactoryInterface;
use Spiral\Tokenizer\ClassesInterface;
use Spiral\Tokenizer\Config\TokenizerConfig;
use Spiral\Tokenizer\EnumsInterface;
use Spiral\Tokenizer\InterfacesInterface;
use Spiral\Tokenizer\Listener\CachedClassesLoader;
use Spiral\Tokenizer\Listener\CachedEnumsLoader;
use Spiral\Tokenizer\Listener\CachedInterfacesLoader;
use Spiral\Tokenizer\Listener\ClassesLoaderInterface;
use Spiral\Tokenizer\Listener\EnumsLoaderInterface;
use Spiral\Tokenizer\Listener\InterfacesLoaderInterface;
use Spiral\Tokenizer\Listener\ListenerInvoker;
use Spiral\Tokenizer\TokenizationListenerInterface;
use Spiral\Tokenizer\TokenizerListenerRegistryInterface;

/**
 * The bootloader is responsible for speeding up the static analysis of the application.
 * First of all, it allows use static analysis only ones and use listeners to analyze found classes.
 * Secondly, it allows to cache the result of the analysis for each listener and use it in the future.
 * If you want to have better performance during the application boot, you should use this bootloader.
 */
#[Singleton]
final class TokenizerListenerBootloader extends Bootloader implements TokenizerListenerRegistryInterface
{
    protected const DEPENDENCIES = [
        AttributesBootloader::class,
        TokenizerBootloader::class,
    ];

    protected const SINGLETONS = [
        TokenizerListenerRegistryInterface::class => self::class,
        ClassesLoaderInterface::class => [self::class, 'initCachedClassesLoader'],
        EnumsLoaderInterface::class => [self::class, 'initCachedEnumsLoader'],
        InterfacesLoaderInterface::class => [self::class, 'initCachedInterfacesLoader'],
    ];

    /** @var TokenizationListenerInterface[] */
    private array $listeners = [];

    /** @var array<class-string<TokenizationListenerInterface>> */
    private array $listenerClasses = [];

    public function addListener(TokenizationListenerInterface $listener): void
    {
        $this->listeners[] = $listener;
        $this->listenerClasses[] = $listener::class;
    }

    public function init(AbstractKernel $kernel): void
    {
        $kernel->booting($this->loadClasses(...));
        $kernel->booting($this->loadEnums(...));
        $kernel->booting($this->loadInterfaces(...));
        $kernel->booting($this->finalizeListeners(...));

        $kernel->booted($this->loadClasses(...));
        $kernel->booted($this->loadEnums(...));
        $kernel->booted($this->loadInterfaces(...));
        $kernel->booted($this->finalizeListeners(...));
    }

    public function initCachedClassesLoader(
        FactoryInterface $factory,
        TokenizerConfig $config,
    ): ClassesLoaderInterface {
        return $this->makeCachedLoader($factory, $config, CachedClassesLoader::class);
    }

    public function initCachedEnumsLoader(
        FactoryInterface $factory,
        TokenizerConfig $config,
    ): EnumsLoaderInterface {
        return $this->makeCachedLoader($factory, $config, CachedEnumsLoader::class);
    }

    public function initCachedInterfacesLoader(
        FactoryInterface $factory,
        TokenizerConfig $config,
    ): InterfacesLoaderInterface {
        return $this->makeCachedLoader($factory, $config, CachedInterfacesLoader::class);
    }

    /**
     * @return array<class-string<TokenizationListenerInterface>>
     */
    public function getListenerClasses(): array
    {
        return $this->listenerClasses;
    }

    /**
     * @template T
     *
     * @param class-string<T> $classLoader
     *
     * @return T
     */
    private function makeCachedLoader(
        FactoryInterface $factory,
        TokenizerConfig $config,
        string $classLoader,
    ): mixed {
        // We will use a file memory to cache the classes/enums/interfaces. Because it's available in the runtime.
        // If you want to disable the read cache, you can use the TOKENIZER_CACHE_TARGETS environment variable.
        // In this case the classes/enums/interfaces will be stored in a cache on every bootstrap,
        // but not read from there.
        return $factory->make($classLoader, [
            'memory' => $factory->make(Memory::class, [
                'directory' => $config->getCacheDirectory(),
            ]),
            'readCache' =>  $config->isCacheEnabled(),
        ]);
    }

    private function loadClasses(
        TokenizerConfig $config,
        ClassesInterface $classes,
        ClassesLoaderInterface $loader,
        ListenerInvoker $invoker,
    ): void {
        if ($config->isLoadClassesEnabled()) {
            $this->loadReflections($invoker, $classes->getClasses(...), $loader->loadClasses(...));
        }
    }

    private function loadEnums(
        TokenizerConfig $config,
        EnumsInterface $enums,
        EnumsLoaderInterface $loader,
        ListenerInvoker $invoker,
    ): void {
        if ($config->isLoadEnumsEnabled()) {
            $this->loadReflections($invoker, $enums->getEnums(...), $loader->loadEnums(...));
        }
    }

    private function loadInterfaces(
        TokenizerConfig $config,
        InterfacesInterface $interfaces,
        InterfacesLoaderInterface $loader,
        ListenerInvoker $invoker,
    ): void {
        if ($config->isLoadInterfacesEnabled()) {
            $this->loadReflections($invoker, $interfaces->getInterfaces(...), $loader->loadInterfaces(...));
        }
    }

    /**
     * @param callable(): array<class-string, \ReflectionClass> $reflections
     * @param callable(TokenizationListenerInterface): bool $loader
     */
    private function loadReflections(
        ListenerInvoker $invoker,
        callable $reflections,
        callable $loader,
    ): void {
        $listeners = $this->listeners;

        // First, we check if the listener has been cached. If it has, we will load the classes/enums/interfaces
        // from the cache.
        foreach ($listeners as $i => $listener) {
            if ($loader($listener)) {
                unset($listeners[$i]);
            }
        }

        // If there are no listeners left, we don't need to use static analysis at all and save
        // valuable time.
        if ($listeners === []) {
            return;
        }

        // If there are listeners left, we will use static analysis to find the classes/enums/interfaces.
        // Please note that this is a very expensive operation and should be avoided if possible.
        // Use #[TargetClass] or #[TargetAttribute] attributes in your listeners to cache the classes/enums/interfaces.
        $classes = $reflections();
        foreach ($listeners as $listener) {
            $invoker->invoke($listener, $classes);
        }
    }

    private function finalizeListeners(): void
    {
        foreach ($this->listeners as $listener) {
            $listener->finalize();
        }
        // We don't need the listeners anymore, so we will clear them from memory.
        $this->listeners = [];
    }
}
