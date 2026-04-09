<?php

declare(strict_types=1);

namespace Spiral\Tests\Config;

use PHPUnit\Framework\TestCase;
use Spiral\Config\ConfigManager;
use Spiral\Config\Loader\DirectoriesRepository;
use Spiral\Config\Loader\FileLoaderRegistry;
use Spiral\Config\Loader\JsonLoader;
use Spiral\Config\Loader\PhpLoader;
use Spiral\Config\Loader\SingleFileStrategyLoader;
use Spiral\Core\Container;

abstract class BaseTestCase extends TestCase
{
    protected Container $container;

    protected function setUp(): void
    {
        $this->container = new Container();
    }

    protected function getFactory(?string $directory = null, bool $strict = true): ConfigManager
    {
        if (\is_null($directory)) {
            $directory = __DIR__ . '/fixtures';
        }

        return new ConfigManager(
            loader: new SingleFileStrategyLoader(
                directories: new DirectoriesRepository([$directory]),
                fileLoader: new FileLoaderRegistry([
                    'php' => $this->container->get(PhpLoader::class),
                    'json' => $this->container->get(JsonLoader::class),
                ]),
            ),
            strict: $strict,
        );
    }
}
