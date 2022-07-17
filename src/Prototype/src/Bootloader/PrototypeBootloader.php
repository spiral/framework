<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Prototype\Bootloader;

use Spiral\Core\Container\SingletonInterface;
use Spiral\Boot\Bootloader\CoreBootloader;
use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\Rules\English\InflectorFactory;
use Spiral\Prototype\Command\DumpCommand;
use Spiral\Prototype\Command\ListCommand;
use Spiral\Prototype\Command\InjectCommand;
use Cycle\ORM\SchemaInterface;
use Cycle\ORM\Select\Repository;
use Cycle\ORM;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Spiral\Attributes\ReaderInterface;
use Spiral\Boot\Bootloader;
use Spiral\Boot\MemoryInterface;
use Spiral\Bootloader\AttributesBootloader;
use Spiral\Console\Bootloader\ConsoleBootloader;
use Spiral\Core\Container;
use Spiral\Prototype\Annotation\Prototyped;
use Spiral\Prototype\Command;
use Spiral\Prototype\PrototypeRegistry;
use Spiral\Tokenizer\ClassLocator;

/**
 * Manages ide-friendly container injections via PrototypeTrait.
 */
final class PrototypeBootloader extends Bootloader\Bootloader implements SingletonInterface
{
    protected const DEPENDENCIES = [
        CoreBootloader::class,
        ConsoleBootloader::class,
        AttributesBootloader::class,
    ];

    // Default spiral specific shortcuts, automatically checked on existence.
    private const DEFAULT_SHORTCUTS = [
        'app'          => ['resolve' => 'Spiral\Boot\KernelInterface'],
        'classLocator' => 'Spiral\Tokenizer\ClassesInterface',
        'console'      => 'Spiral\Console\Console',
        'container'    => 'Psr\Container\ContainerInterface',
        'db'           => 'Cycle\Database\DatabaseInterface',
        'dbal'         => 'Cycle\Database\DatabaseProviderInterface',
        'encrypter'    => 'Spiral\Encrypter\EncrypterInterface',
        'env'          => 'Spiral\Boot\EnvironmentInterface',
        'files'        => 'Spiral\Files\FilesInterface',
        'guard'        => 'Spiral\Security\GuardInterface',
        'http'         => 'Spiral\Http\Http',
        'i18n'         => 'Spiral\Translator\TranslatorInterface',
        'input'        => 'Spiral\Http\Request\InputManager',
        'session'      => ['resolve' => 'Spiral\Session\SessionScope', 'with' => ['Spiral\Session\SessionInterface']],
        'cookies'      => 'Spiral\Cookies\CookieManager',
        'logger'       => 'Psr\Log\LoggerInterface',
        'logs'         => 'Spiral\Logger\LogsInterface',
        'memory'       => 'Spiral\Boot\MemoryInterface',
        'orm'          => 'Cycle\ORM\ORMInterface',
        'paginators'   => 'Spiral\Pagination\PaginationProviderInterface',
        'queue'        => 'Spiral\Queue\QueueInterface',
        'queueManager' => 'Spiral\Queue\QueueConnectionProviderInterface',
        'request'      => 'Spiral\Http\Request\InputManager',
        'response'     => 'Spiral\Http\ResponseWrapper',
        'router'       => 'Spiral\Router\RouterInterface',
        'server'       => 'Spiral\Goridge\RPC',
        'snapshots'    => 'Spiral\Snapshots\SnapshotterInterface',
        'storage'      => 'Spiral\Storage\BucketInterface',
        'validator'    => 'Spiral\Validation\ValidationInterface',
        'views'        => 'Spiral\Views\ViewsInterface',
        'auth'         => ['resolve' => 'Spiral\Auth\AuthScope', 'with' => ['Spiral\Auth\AuthContextInterface']],
        'authTokens'   => 'Spiral\Auth\TokenStorageInterface',
        'cache'        => 'Psr\SimpleCache\CacheInterface',
        'cacheManager' => 'Spiral\Cache\CacheStorageProviderInterface',
    ];

    private MemoryInterface $memory;

    private PrototypeRegistry $registry;

    private Inflector $inflector;

    public function __construct(MemoryInterface $memory, PrototypeRegistry $registry)
    {
        $this->memory = $memory;
        $this->registry = $registry;
        $this->inflector = (new InflectorFactory())->build();
    }

    public function boot(ConsoleBootloader $console, ContainerInterface $container): void
    {
        $console->addCommand(DumpCommand::class);
        $console->addCommand(ListCommand::class);
        $console->addCommand(InjectCommand::class);

        $console->addConfigureSequence(
            'prototype:dump',
            '<fg=magenta>[prototype]</fg=magenta> <fg=cyan>actualizing prototype injections...</fg=cyan>'
        );

        $console->addUpdateSequence(
            'prototype:dump',
            '<fg=magenta>[prototype]</fg=magenta> <fg=cyan>actualizing prototype injections...</fg=cyan>'
        );

        $this->initDefaults($container);
        $this->initCycle($container);
        $this->initAnnotations($container, false);
    }

    public function bindProperty(string $property, string $type): void
    {
        $this->registry->bindProperty($property, $type);
    }

    public function defineSingletons(): array
    {
        return [PrototypeRegistry::class => $this->registry];
    }

    public function initAnnotations(ContainerInterface $container, bool $reset = false): void
    {
        $prototyped = $this->memory->loadData('prototyped');
        if (!$reset && $prototyped !== null) {
            foreach ($prototyped as $property => $class) {
                $this->bindProperty($property, $class);
            }

            return;
        }

        /** @var ClassLocator $locator */
        $locator = $container->get(ClassLocator::class);
        $reader = $container->get(ReaderInterface::class);

        $prototyped = [];
        foreach ($locator->getClasses() as $class) {
            $meta = $reader->firstClassMetadata($class, Prototyped::class);

            if ($meta === null) {
                continue;
            }

            $prototyped[$meta->property] = $class->getName();
            $this->bindProperty($meta->property, $class->getName());
        }

        $this->memory->saveData('prototyped', $prototyped);
    }

    /**
     * @deprecated since v2.12. Will be moved to spiral/cycle-bridge and removed in v3.0
     */
    public function initCycle(ContainerInterface $container): void
    {
        if (!$container->has(SchemaInterface::class)) {
            return;
        }

        /** @var ORM\SchemaInterface|null $schema */
        $schema = $container->get(SchemaInterface::class);
        if ($schema === null) {
            return;
        }

        foreach ($schema->getRoles() as $role) {
            $repository = $schema->define($role, SchemaInterface::REPOSITORY);
            if ($repository === Repository::class || $repository === null) {
                // default repository can not be wired
                continue;
            }

            $this->bindProperty($this->inflector->pluralize($role), $repository);
        }
    }

    private function initDefaults(ContainerInterface $container): void
    {
        foreach (self::DEFAULT_SHORTCUTS as $property => $shortcut) {
            if (is_array($shortcut) && isset($shortcut['resolve'])) {
                if (isset($shortcut['with'])) {
                    // check dependencies
                    foreach ($shortcut['with'] as $dep) {
                        if (!class_exists($dep, true) && !interface_exists($dep, true)) {
                            continue 2;
                        }
                    }
                }

                try {
                    $target = $container->get($shortcut['resolve']);
                    if (is_object($target)) {
                        $this->bindProperty($property, get_class($target));
                    }
                } catch (ContainerExceptionInterface $e) {
                    continue;
                }

                continue;
            }

            if (
                is_string($shortcut)
                && (class_exists($shortcut, true)
                    || interface_exists($shortcut, true))
            ) {
                $this->bindProperty($property, $shortcut);
            }
        }
    }
}
