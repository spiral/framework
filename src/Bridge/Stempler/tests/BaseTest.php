<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Stempler;

use PHPUnit\Framework\TestCase;
use Spiral\Boot\BootloadManager;
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
use Spiral\Views\ViewManager;
use Spiral\Views\ViewsInterface;

abstract class BaseTest extends TestCase
{
    public const BOOTLOADERS = [
        StemplerBootloader::class,
        PrettyPrintBootloader::class
    ];
    /** @var Container */
    protected $container;
    /**
     * @var BootloadManager
     */
    protected $app;

    public function setUp(): void
    {
        $this->container = $this->container ?? new Container();
        $this->container->bind(EnvironmentInterface::class, new Environment());
        $this->container->bind(
            DirectoriesInterface::class,
            new Directories(
                [
                    'app'   => __DIR__ . '/fixtures',
                    'cache' => __DIR__ . '/cache'
                ]
            )
        );

        $this->container->bind(ConfigsInterface::class, ConfiguratorInterface::class);
        $this->container->bind(
            ConfiguratorInterface::class,
            new ConfigManager(
                new DirectoryLoader(__DIR__ . '/config/', [
                    'php'  => $this->container->get(PhpLoader::class),
                    'json' => $this->container->get(JsonLoader::class),
                ]),
                true
            )
        );

        $this->container->bind(ViewsInterface::class, ViewManager::class);

        $this->app = new BootloadManager($this->container);
        $this->app->bootload(static::BOOTLOADERS);
    }

    protected function getViews(): ViewManager
    {
        return $this->container->get(ViewsInterface::class);
    }

    protected function getStempler(): StemplerEngine
    {
        return $this->container->get(ViewsInterface::class)->getEngines()[0];
    }
}
