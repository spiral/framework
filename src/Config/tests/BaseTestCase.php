<?php

declare(strict_types=1);

namespace Spiral\Tests\Config;

use PHPUnit\Framework\TestCase;
use Spiral\Config\ConfigManager;
use Spiral\Config\Loader\DirectoryLoader;
use Spiral\Config\Loader\JsonLoader;
use Spiral\Config\Loader\PhpLoader;
use Spiral\Core\Container;

abstract class BaseTestCase extends TestCase
{
    /**
     * @var Container
     */
    protected $container;

    protected function setUp(): void
    {
        $this->container = new Container();
    }

    protected function getFactory(?string $directory = null, bool $strict = true): ConfigManager
    {
        if (is_null($directory)) {
            $directory = __DIR__ . '/fixtures';
        }

        return new ConfigManager(
            new DirectoryLoader($directory, [
                'php'  => $this->container->get(PhpLoader::class),
                'json' => $this->container->get(JsonLoader::class),
            ]),
            $strict,
        );
    }
}
