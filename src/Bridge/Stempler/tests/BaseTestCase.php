<?php

declare(strict_types=1);

namespace Spiral\Tests\Stempler;

use Spiral\Core\Container;
use Spiral\Stempler\Bootloader\PrettyPrintBootloader;
use Spiral\Stempler\Bootloader\StemplerBootloader;
use Spiral\Stempler\StemplerEngine;
use Spiral\Testing\TestCase;
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

    protected function setUp(): void
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
