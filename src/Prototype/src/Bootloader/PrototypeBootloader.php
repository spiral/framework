<?php

declare(strict_types=1);

namespace Spiral\Prototype\Bootloader;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Spiral\Auth\AuthContextInterface;
use Spiral\Auth\AuthScope;
use Spiral\Auth\TokenStorageInterface;
use Spiral\Boot\Bootloader;
use Spiral\Boot\Bootloader\CoreBootloader;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Boot\KernelInterface;
use Spiral\Boot\MemoryInterface;
use Spiral\Bootloader\Attributes\AttributesBootloader;
use Spiral\Broadcasting\BroadcastInterface;
use Spiral\Cache\CacheStorageProviderInterface;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Patch\Append;
use Spiral\Console\Bootloader\ConsoleBootloader;
use Spiral\Console\Console;
use Spiral\Cookies\CookieManager;
use Spiral\Core\Attribute\Singleton;
use Spiral\Encrypter\EncrypterInterface;
use Spiral\Exceptions\ExceptionHandlerInterface;
use Spiral\Files\FilesInterface;
use Spiral\Http\Http;
use Spiral\Http\Request\InputManager;
use Spiral\Http\ResponseWrapper;
use Spiral\Logger\LogsInterface;
use Spiral\Pagination\PaginationProviderInterface;
use Spiral\Prototype\Command;
use Spiral\Prototype\Command\DumpCommand;
use Spiral\Prototype\Command\InjectCommand;
use Spiral\Prototype\Command\ListCommand;
use Spiral\Prototype\Command\UsageCommand;
use Spiral\Prototype\Config\PrototypeConfig;
use Spiral\Prototype\PrototypeLocatorListener;
use Spiral\Prototype\PrototypeRegistry;
use Spiral\Queue\QueueConnectionProviderInterface;
use Spiral\Queue\QueueInterface;
use Spiral\Router\RouterInterface;
use Spiral\Security\GuardInterface;
use Spiral\Serializer\SerializerManager;
use Spiral\Session\SessionInterface;
use Spiral\Session\SessionScope;
use Spiral\Snapshots\SnapshotterInterface;
use Spiral\Storage\BucketInterface;
use Spiral\Tokenizer\Bootloader\TokenizerListenerBootloader;
use Spiral\Tokenizer\ClassesInterface;
use Spiral\Tokenizer\TokenizerListenerRegistryInterface;
use Spiral\Translator\TranslatorInterface;
use Spiral\Validation\ValidationInterface;
use Spiral\Views\ViewsInterface;

/**
 * Manages ide-friendly container injections via PrototypeTrait.
 */
#[Singleton]
final class PrototypeBootloader extends Bootloader\Bootloader
{
    protected const DEPENDENCIES = [
        CoreBootloader::class,
        TokenizerListenerBootloader::class,
        AttributesBootloader::class,
    ];

    protected const SINGLETONS = [
        PrototypeRegistry::class => [self::class, 'initRegistry'],
    ];

    // Default spiral specific shortcuts, automatically checked on existence.
    private const DEFAULT_SHORTCUTS = [
        'app' => ['resolve' => KernelInterface::class],
        'classLocator' => ClassesInterface::class,
        'console' => Console::class,
        'broadcast' => BroadcastInterface::class,
        'container' => ContainerInterface::class,
        'encrypter' => EncrypterInterface::class,
        'env' => EnvironmentInterface::class,
        'files' => FilesInterface::class,
        'guard' => GuardInterface::class,
        'http' => Http::class,
        'i18n' => TranslatorInterface::class,
        'input' => InputManager::class,
        'session' => [
            'resolve' => SessionScope::class,
            'with' => [SessionInterface::class],
        ],
        'cookies' => CookieManager::class,
        'logger' => LoggerInterface::class,
        'logs' => LogsInterface::class,
        'memory' => MemoryInterface::class,
        'paginators' => PaginationProviderInterface::class,
        'queue' => QueueInterface::class,
        'queueManager' => QueueConnectionProviderInterface::class,
        'request' => InputManager::class,
        'response' => ResponseWrapper::class,
        'router' => RouterInterface::class,
        'snapshots' => SnapshotterInterface::class,
        'storage' => BucketInterface::class,
        'serializer' => SerializerManager::class,
        'validator' => ValidationInterface::class,
        'views' => ViewsInterface::class,
        'auth' => [
            'resolve' => AuthScope::class,
            'with' => [AuthContextInterface::class],
        ],
        'authTokens' => TokenStorageInterface::class,
        'cache' => CacheInterface::class,
        'cacheManager' => CacheStorageProviderInterface::class,
        'exceptionHandler' => ExceptionHandlerInterface::class,
    ];

    public function __construct(
        private readonly ConfiguratorInterface $config
    ) {
    }

    public function init(ConsoleBootloader $console): void
    {
        $console->addCommand(DumpCommand::class);
        $console->addCommand(ListCommand::class);
        $console->addCommand(UsageCommand::class);
        $console->addCommand(InjectCommand::class);

        $console->addConfigureSequence(
            'prototype:dump',
            '<fg=magenta>[prototype]</fg=magenta> <fg=cyan>actualizing prototype injections...</fg=cyan>'
        );

        $console->addUpdateSequence(
            'prototype:dump',
            '<fg=magenta>[prototype]</fg=magenta> <fg=cyan>actualizing prototype injections...</fg=cyan>'
        );

        $this->config->setDefaults(
            PrototypeConfig::CONFIG,
            [
                'bindings' => self::DEFAULT_SHORTCUTS,
            ]
        );
    }

    public function boot(
        TokenizerListenerRegistryInterface $listenerRegistry,
        PrototypeLocatorListener $listener
    ): void {
        $listenerRegistry->addListener($listener);
    }

    public function bindProperty(string $property, string $type): void
    {
        $this->config->modify(PrototypeConfig::CONFIG, new Append('bindings', $property, $type));
    }

    private function initRegistry(PrototypeConfig $config, ContainerInterface $container): PrototypeRegistry
    {
        $registry = new PrototypeRegistry($container);

        foreach ($config->getBindings() as $property => $shortcut) {
            if (\is_array($shortcut) && isset($shortcut['resolve'])) {
                if (isset($shortcut['with'])) {
                    // check dependencies
                    foreach ($shortcut['with'] as $dep) {
                        if (!\class_exists($dep, true) && !\interface_exists($dep, true)) {
                            continue 2;
                        }
                    }
                }

                try {
                    $target = $container->get($shortcut['resolve']);
                    if (\is_object($target)) {
                        $registry->bindProperty($property, $target::class);
                    }
                } catch (ContainerExceptionInterface) {
                    continue;
                }

                continue;
            }

            if (\is_string($shortcut) && (\class_exists($shortcut, true) || \interface_exists($shortcut, true))) {
                $registry->bindProperty($property, $shortcut);
            }
        }

        return $registry;
    }
}
