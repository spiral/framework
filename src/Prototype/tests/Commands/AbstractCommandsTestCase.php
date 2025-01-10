<?php

declare(strict_types=1);

namespace Spiral\Tests\Prototype\Commands;

use PHPUnit\Framework\TestCase;
use Spiral\Framework\Kernel;
use Spiral\Tests\Prototype\Fixtures\TestApp;
use Spiral\Tests\Prototype\Storage;

abstract class AbstractCommandsTestCase extends TestCase
{
    protected const STORE = [
        'TestClass.php',
        'TestEmptyClass.php',
        'ChildClass.php',
        'ChildWithConstructorClass.php',
        'WithConstructor.php',
        'OptionalConstructorArgsClass.php',
        'InheritedInjection/ParentClass.php',
        'InheritedInjection/MiddleClass.php',
        'InheritedInjection/ChildClass.php',
    ];

    protected TestApp $app;
    protected array $buf = [];
    private readonly Storage $storage;

    protected function setUp(): void
    {
        if (!\class_exists(Kernel::class)) {
            $this->markTestSkipped('A "spiral/framework" dependency is required to run these tests');
        }

        $this->app = TestApp::create([
            'root'   => $this->dir(),
            'config' => $this->dir(),
            'app'    => $this->dir(),
            'cache'  => sys_get_temp_dir()
        ], false)->run();

        $this->storage = new Storage($this->dir() . '/Fixtures/');

        foreach (static::STORE as $name) {
            $this->storage->store($name);
        }
    }

    protected function tearDown(): void
    {
        foreach (static::STORE as $name) {
            $this->storage->restore($name);
        }
    }

    private function dir(): string
    {
        return dirname(__DIR__);
    }
}
