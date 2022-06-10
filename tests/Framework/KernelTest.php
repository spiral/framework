<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework;

use Spiral\Boot\Exception\BootException;
use Spiral\App\TestApp;
use Spiral\Core\Container;
use stdClass;

class KernelTest extends BaseTest
{
    public const MAKE_APP_ON_STARTUP = false;

    public function testBypassEnvironmentToConfig(): void
    {
        $this->initApp([
            'TEST_VALUE' => 'HELLO WORLD',
        ]);

        $this->assertConfigMatches('test', [
            'key' => 'HELLO WORLD',
        ]);
    }

    public function testGetEnv(): void
    {
        $this->initApp([
            'DEBUG' => true,
            'ENV' => 123,
        ]);

        $this->assertEnvironmentValueSame('ENV', 123);
    }

    public function testNoRootDirectory(): void
    {
        $this->expectException(BootException::class);

        $this->initApp();

        TestApp::create([], false)->run();
    }

    public function testCustomContainer(): void
    {
        $this->initApp();
        $container = new Container();
        $container->bind('foofoo', new stdClass());

        $app = TestApp::create([
            'root' => __DIR__.'/../..',
        ], container: $container);

        $this->assertSame($container, $app->getContainer());
        $this->assertInstanceOf(stdClass::class, $app->getContainer()->get('foofoo'));
    }
}
