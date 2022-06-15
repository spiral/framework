<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework;

use Spiral\App\TestApp;
use Spiral\Core\Container;

abstract class BaseTest extends \Spiral\Testing\TestCase
{
    public function rootDirectory(): string
    {
        return __DIR__.'/../';
    }

    public function createAppInstance(Container $container = new Container()): TestApp
    {
        return TestApp::create(
            $this->defineDirectories($this->rootDirectory()),
            false
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->cleanUpRuntimeDirectory();
    }
}
