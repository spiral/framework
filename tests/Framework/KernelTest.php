<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Framework;

use Spiral\Boot\Environment;
use Spiral\Boot\Exception\BootException;
use Spiral\Config\ConfiguratorInterface;
use Spiral\App\TestApp;

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
}
