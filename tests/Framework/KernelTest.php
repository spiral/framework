<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework;

use Spiral\Boot\Exception\BootException;
use Spiral\Config\ConfiguratorInterface;
use Spiral\App\TestApp;
use Spiral\Core\Container;
use stdClass;

class KernelTest extends BaseTest
{
    public function testBypassEnvironmentToConfig(): void
    {
        $configs = $this->makeApp([
            'TEST_VALUE' => 'HELLO WORLD'
        ])->get(ConfiguratorInterface::class);

        $this->assertSame([
            'key' => 'HELLO WORLD'
        ], $configs->getConfig('test'));
    }

    public function testGetEnv(): void
    {
        $app = $this->makeApp([
            'DEBUG' => true,
            'ENV'   => 123
        ]);

        $this->assertSame(123, $app->getEnvironment()->get('ENV'));
    }

    public function testNoRootDirectory(): void
    {
        $this->expectException(BootException::class);

        TestApp::create([], false)->run();
    }

    public function testCustomContainer(): void
    {
        $container = new Container();
        $container->bind('foofoo', new stdClass());

        $app = TestApp::create([
            'root'    => __DIR__ . '/../..',
        ], container: $container);

        $this->assertSame($container, $app->getContainer());
        $this->assertInstanceOf(stdClass::class, $app->get('foofoo'));
    }
}
