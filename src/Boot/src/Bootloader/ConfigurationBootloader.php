<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Boot\Bootloader;

use Spiral\Boot\DirectoriesInterface;
use Spiral\Config\ConfigManager;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Loader\DirectoryLoader;
use Spiral\Config\Loader\FileLoaderInterface;
use Spiral\Config\Loader\JsonLoader;
use Spiral\Config\Loader\PhpLoader;
use Spiral\Core\ConfigsInterface;
use Spiral\Core\Container;

/**
 * Bootloads core services.
 */
final class ConfigurationBootloader extends Bootloader
{
    protected const SINGLETONS = [
        // configuration
        ConfigsInterface::class      => ConfiguratorInterface::class,
        ConfiguratorInterface::class => ConfigManager::class,
        ConfigManager::class         => [self::class, 'configManager'],
    ];

    /** @var ConfiguratorInterface */
    private $configurator;

    /** @var FileLoaderInterface[] */
    private $loaders;

    /** @var DirectoriesInterface */
    private $directories;

    /** @var Container */
    private $container;

    public function __construct(DirectoriesInterface $directories, Container $container)
    {
        $this->loaders = [
            'php' => $container->get(PhpLoader::class),
            'json' => $container->get(JsonLoader::class),
        ];

        $this->directories = $directories;
        $this->container = $container;
        $this->configurator = $this->createConfigManager();
    }

    public function addLoader(string $ext, FileLoaderInterface $loader): void
    {
        if (!isset($this->loaders[$ext]) || get_class($this->loaders[$ext]) !== get_class($loader)) {
            $this->loaders[$ext] = $loader;
            $this->container->bindSingleton(ConfigManager::class, $this->createConfigManager());
        }
    }

    private function createConfigManager(): ConfiguratorInterface
    {
        return new ConfigManager(
            new DirectoryLoader($this->directories->get('config'), $this->loaders),
            true
        );
    }

    private function configManager(): ConfiguratorInterface
    {
        return $this->configurator;
    }
}
