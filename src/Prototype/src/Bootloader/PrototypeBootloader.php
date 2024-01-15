<?php

declare(strict_types=1);

namespace Spiral\Prototype\Bootloader;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Spiral\Boot\Bootloader;
use Spiral\Boot\MemoryInterface;
use Spiral\Bootloader\Attributes\AttributesBootloader;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Patch\Append;
use Spiral\Console\Bootloader\ConsoleBootloader;
use Spiral\Core\Attribute\Singleton;
use Spiral\Prototype\Command;
use Spiral\Prototype\Config\PrototypeConfig;
use Spiral\Prototype\PrototypeLocatorListener;
use Spiral\Prototype\PrototypeRegistry;
use Spiral\Tokenizer\Bootloader\TokenizerListenerBootloader;
use Spiral\Tokenizer\TokenizerListenerRegistryInterface;

/**
 * Manages ide-friendly container injections via PrototypeTrait.
 */
#[Singleton]
final class PrototypeBootloader extends Bootloader\Bootloader
{
    protected const DEPENDENCIES = [
        Bootloader\CoreBootloader::class,
        TokenizerListenerBootloader::class,
        AttributesBootloader::class,
    ];

    protected const SINGLETONS = [
        PrototypeRegistry::class => [self::class, 'initRegistry'],
    ];

    // Default spiral specific shortcuts, automatically checked on existence.
    private const DEFAULT_SHORTCUTS = [
        'app' => ['resolve' => \Spiral\Boot\KernelInterface::class],
        'classLocator' => \Spiral\Tokenizer\ClassesInterface::class,
        'console' => \Spiral\Console\Console::class,
        'broadcast' => \Spiral\Broadcasting\BroadcastInterface::class,
        'container' => ContainerInterface::class,
        'encrypter' => \Spiral\Encrypter\EncrypterInterface::class,
        'env' => \Spiral\Boot\EnvironmentInterface::class,
        'files' => \Spiral\Files\FilesInterface::class,
        'guard' => \Spiral\Security\GuardInterface::class,
        'http' => \Spiral\Http\Http::class,
        'i18n' => \Spiral\Translator\TranslatorInterface::class,
        'input' => \Spiral\Http\Request\InputManager::class,
        'session' => [
            'resolve' => \Spiral\Session\SessionScope::class,
            'with' => [\Spiral\Session\SessionInterface::class],
        ],
        'cookies' => \Spiral\Cookies\CookieManager::class,
        'logger' => \Psr\Log\LoggerInterface::class,
        'logs' => \Spiral\Logger\LogsInterface::class,
        'memory' => MemoryInterface::class,
        'paginators' => \Spiral\Pagination\PaginationProviderInterface::class,
        'queue' => \Spiral\Queue\QueueInterface::class,
        'queueManager' => \Spiral\Queue\QueueConnectionProviderInterface::class,
        'request' => \Spiral\Http\Request\InputManager::class,
        'response' => \Spiral\Http\ResponseWrapper::class,
        'router' => \Spiral\Router\RouterInterface::class,
        'snapshots' => \Spiral\Snapshots\SnapshotterInterface::class,
        'storage' => \Spiral\Storage\BucketInterface::class,
        'serializer' => \Spiral\Serializer\SerializerManager::class,
        'validator' => \Spiral\Validation\ValidationInterface::class,
        'views' => \Spiral\Views\ViewsInterface::class,
        'auth' => [
            'resolve' => \Spiral\Auth\AuthScope::class,
            'with' => [\Spiral\Auth\AuthContextInterface::class],
        ],
        'authTokens' => \Spiral\Auth\TokenStorageInterface::class,
        'cache' => \Psr\SimpleCache\CacheInterface::class,
        'cacheManager' => \Spiral\Cache\CacheStorageProviderInterface::class,
        'exceptionHandler' => \Spiral\Exceptions\ExceptionHandlerInterface::class,
    ];

    public function __construct(
        private readonly ConfiguratorInterface $config
    ) {
    }

    public function init(ConsoleBootloader $console): void
    {
        $console->addCommand(Command\DumpCommand::class);
        $console->addCommand(Command\ListCommand::class);
        $console->addCommand(Command\UsageCommand::class);
        $console->addCommand(Command\InjectCommand::class);

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
