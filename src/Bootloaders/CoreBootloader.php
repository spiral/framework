<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Framework\Bootloaders;

use Spiral\Config\ConfigFactory;
use Spiral\Config\Loaders\DirectoryLoader;
use Spiral\Core\Bootloaders\Bootloader;
use Spiral\Core\ConfiguratorInterface;
use Spiral\Core\FactoryInterface;
use Spiral\Core\MemoryInterface;
use Spiral\Files\Files;
use Spiral\Files\FilesInterface;
use Spiral\Framework\DirectoriesInterface;
use Spiral\Framework\Environment;
use Spiral\Framework\EnvironmentInterface;
use Spiral\Framework\Memory;

final class CoreBootloader extends Bootloader
{
    const SINGLETONS = [
        FilesInterface::class        => Files::class,
        EnvironmentInterface::class  => Environment::class,
        MemoryInterface::class       => [self::class, 'memory'],
        ConfiguratorInterface::class => [self::class, 'configFactory'],
    ];

    /**
     * @param DirectoriesInterface $directories
     * @param FactoryInterface     $factory
     * @return ConfiguratorInterface
     */
    protected function configFactory(
        DirectoriesInterface $directories,
        FactoryInterface $factory
    ): ConfiguratorInterface {
        return new ConfigFactory(new DirectoryLoader($directories->get('config'), $factory), true);
    }

    /**
     * @param DirectoriesInterface $directories
     * @param FilesInterface       $files
     * @return MemoryInterface
     */
    protected function memory(
        DirectoriesInterface $directories,
        FilesInterface $files
    ): MemoryInterface {
        return new Memory($directories->get('runtime'), $files);
    }
}