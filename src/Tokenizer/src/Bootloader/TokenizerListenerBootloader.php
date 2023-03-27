<?php

declare(strict_types=1);

namespace Spiral\Tokenizer\Bootloader;

use Spiral\Boot\AbstractKernel;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\DirectoriesInterface;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Boot\Memory;
use Spiral\Boot\NullMemory;
use Spiral\Bootloader\Attributes\AttributesBootloader;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Core\FactoryInterface;
use Spiral\Tokenizer\ClassesInterface;
use Spiral\Tokenizer\Config\TokenizerConfig;
use Spiral\Tokenizer\Listener\CachedClassesLoader;
use Spiral\Tokenizer\Listener\ClassesLoaderInterface;
use Spiral\Tokenizer\Listener\ListenerInvoker;
use Spiral\Tokenizer\TokenizationListenerInterface;
use Spiral\Tokenizer\TokenizerListenerRegistryInterface;

/**
 * The bootloader is responsible for speeding up the static analysis of the application.
 * First of all, it allows use static analysis only ones and use listeners to analyze found classes.
 * Secondly, it allows to cache the result of the analysis for each listener and use it in the future.
 * If you want to have better performance during the application boot, you should use this bootloader.
 */
final class TokenizerListenerBootloader extends Bootloader implements
    SingletonInterface,
    TokenizerListenerRegistryInterface
{
    protected const DEPENDENCIES = [
        AttributesBootloader::class,
        TokenizerBootloader::class,
    ];

    protected const SINGLETONS = [
        TokenizerListenerRegistryInterface::class => self::class,
        ClassesLoaderInterface::class => [self::class, 'initCachedClassesLoader'],
    ];

    /** @var TokenizationListenerInterface[] */
    private array $listeners = [];

    public function addListener(TokenizationListenerInterface $listener): void
    {
        $this->listeners[] = $listener;
    }

    public function boot(AbstractKernel $kernel): void
    {
        $kernel->booted(function (
            ClassesInterface $classes,
            ClassesLoaderInterface $loader,
            ListenerInvoker $invoker,
        ): void {
            // First, we check if the listener has been cached. If it has, we will load the classes
            // from the cache.
            foreach ($this->listeners as $i => $listener) {
                if ($loader->loadClasses($listener)) {
                    unset($this->listeners[$i]);
                }
            }

            // If there are no listeners left, we don't need to use static analysis at all and save
            // valuable time.
            if ($this->listeners === []) {
                return;
            }

            // If there are listeners left, we will use static analysis to find the classes.
            // Please note that this is a very expensive operation and should be avoided if possible.
            // Use #[TargetClass] or #[TargetAttribute] attributes in your listeners to cache the classes.
            $classes = $classes->getClasses();
            foreach ($this->listeners as $listener) {
                $invoker->invoke($listener, $classes);
            }

            // We don't need the listeners anymore, so we will clear them from memory.
            $this->listeners = [];
        });
    }

    public function initCachedClassesLoader(
        FactoryInterface $factory,
        DirectoriesInterface $dirs,
        EnvironmentInterface $env,
        TokenizerConfig $config,
    ): ClassesLoaderInterface {
        // We will use a file memory to cache the classes. Because it's available in the runtime.
        // If you want to disable the read cache, you can use the TOKENIZER_CACHE_TARGETS environment variable.
        // In this case the classes will be stored in a cache on every bootstrap, but not read from there.
        return $factory->make(CachedClassesLoader::class, [
            'memory' => $factory->make(Memory::class, [
                'directory' => $config->getCacheDirectory() ?? $dirs->get('runtime') . 'cache/listeners',
            ]),
            'readCache' => \filter_var(
                $env->get('TOKENIZER_CACHE_TARGETS', $config->isCacheEnabled()),
                \FILTER_VALIDATE_BOOL
            ),
        ]);
    }
}
