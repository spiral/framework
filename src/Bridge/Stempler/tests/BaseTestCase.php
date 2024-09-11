<?php

declare(strict_types=1);

namespace Spiral\Tests\Stempler;

use Spiral\Boot\BootloadManager\StrategyBasedBootloadManager;
use Spiral\Boot\BootloadManager\DefaultInvokerStrategy;
use Spiral\Boot\BootloadManager\Initializer;
use Spiral\Boot\Directories;
use Spiral\Boot\DirectoriesInterface;
use Spiral\Boot\Environment;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Config\ConfigManager;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Loader\DirectoryLoader;
use Spiral\Config\Loader\JsonLoader;
use Spiral\Config\Loader\PhpLoader;
use Spiral\Core\ConfigsInterface;
use Spiral\Core\Container;
use Spiral\Stempler\Bootloader\PrettyPrintBootloader;
use Spiral\Stempler\Bootloader\StemplerBootloader;
use Spiral\Stempler\StemplerEngine;
use Spiral\Testing\TestCase;
use Spiral\Views\ViewManager;
use Spiral\Views\ViewsInterface;

abstract class BaseTestCase extends TestCase
{
    protected Container $container;

    public function defineBootloaders(): array
    {
        return [
            StemplerBootloader::class,
            PrettyPrintBootloader::class
        ];
    }

    public function defineDirectories(string $root): array
    {
        return [
            'app'   => __DIR__ . '/fixtures',
            'cache' => __DIR__ . '/cache',
            'config' => __DIR__ . '/config',
        ] + parent::defineDirectories($root);
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->container = $this->getContainer();
        // $this->container->bind(EnvironmentInterface::class, new Environment());
        // $this->container->bind(ViewsInterface::class, ViewManager::class);
    }

    protected function getStempler(): StemplerEngine
    {
        return $this->container->get(ViewsInterface::class)->getEngines()[0];
    }
}
