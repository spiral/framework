<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework;

use Spiral\App\TestApp;
use Spiral\Core\Container;
use Spiral\Testing\Traits\InteractsWithCore;

abstract class BaseTestCase extends \Spiral\Testing\TestCase
{
    use InteractsWithCore;

    private array $disabledBootloaders = [];

    public function rootDirectory(): string
    {
        return \realpath(__DIR__.'/../');
    }

    public function createAppInstance(Container $container = new Container()): TestApp
    {
        return TestApp::create(
            directories: $this->defineDirectories($this->rootDirectory()),
            handleErrors: false,
            container: $container,
        )->disableBootloader(...$this->disabledBootloaders);
    }

    public function withDisabledBootloaders(string ...$bootloader): self
    {
        $this->disabledBootloaders = $bootloader;

        return $this;
    }

    public function getTestEnvVariables(): array
    {
        return [
            ...static::ENV,
            ...$this->getEnvVariablesFromConfig(),
        ];
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->cleanUpRuntimeDirectory();
    }
}
