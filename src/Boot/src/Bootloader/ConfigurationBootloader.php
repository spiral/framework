<?php

declare(strict_types=1);

namespace Spiral\Boot\Bootloader;

use Psr\Container\ContainerInterface;
use Spiral\Boot\DirectoriesInterface;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Config\ConfigManager;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Loader\ConfigsMergerInterface;
use Spiral\Config\Loader\DirectoriesRepository;
use Spiral\Config\Loader\DirectoriesRepositoryInterface;
use Spiral\Config\Loader\FileLoaderInterface;
use Spiral\Config\Loader\FileLoaderRegistry;
use Spiral\Config\Loader\JsonLoader;
use Spiral\Config\Loader\MergeFileStrategyLoader;
use Spiral\Config\Loader\PhpLoader;
use Spiral\Config\Loader\RecursiveConfigMerger;
use Spiral\Config\Loader\SingleFileStrategyLoader;
use Spiral\Config\LoaderInterface;
use Spiral\Core\ConfigsInterface;

/**
 * Boot core service responsible for configuration loading and management.
 */
final class ConfigurationBootloader extends Bootloader
{
    public function __construct(
        private readonly ContainerInterface $container,
    ) {}

    public function defineSingletons(): array
    {
        return [
            ConfigsInterface::class => ConfiguratorInterface::class,
            ConfiguratorInterface::class => ConfigManager::class,
            FileLoaderRegistry::class => $this->createFileLoader(...),
            ConfigManager::class => $this->createConfigManager(...),
            ConfigsMergerInterface::class => RecursiveConfigMerger::class,
            LoaderInterface::class => $this->createConfigLoader(...),
            DirectoriesRepositoryInterface::class => $this->createDirectories(...),
        ];
    }

    private function createConfigLoader(EnvironmentInterface $env): LoaderInterface
    {
        return match ($env->get('CONFIG_STRATEGY', 'single')) {
            'merge' => $this->container->get(MergeFileStrategyLoader::class),
            default => $this->container->get(SingleFileStrategyLoader::class),
        };
    }

    private function createDirectories(DirectoriesInterface $directories): DirectoriesRepositoryInterface
    {
        return new DirectoriesRepository([
            $directories->get('config'),
        ]);
    }

    private function createFileLoader(PhpLoader $phpLoader, JsonLoader $jsonLoader): FileLoaderRegistry
    {
        return new FileLoaderRegistry([
            'php' => $phpLoader,
            'json' => $jsonLoader,
        ]);
    }

    public function setDirectories(array $directories): void
    {
        $this->container->get(DirectoriesRepositoryInterface::class)->setDirectories($directories);
    }

    public function addLoader(string $ext, FileLoaderInterface $loader): void
    {
        $this->container->get(FileLoaderRegistry::class)->register($ext, $loader);
    }

    private function createConfigManager(LoaderInterface $loader): ConfigManager
    {
        return new ConfigManager(
            loader: $loader,
            strict: true,
        );
    }
}
