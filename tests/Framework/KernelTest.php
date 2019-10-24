<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Framework;

use Spiral\App\TestApp;
use Spiral\Boot\Environment;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Goridge\RPC;
use Spiral\RoadRunner\Worker;

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

    /**
     * @expectedException \Spiral\Boot\Exception\BootException
     */
    public function testNoRootDirectory(): void
    {
        TestApp::init([
        ], new Environment(), false);
    }

    public function testWorker(): void
    {
        /** @var Worker $worker */
        $worker = $this->makeApp([])->get(Worker::class);
        $this->assertInstanceOf(Worker::class, $worker);
    }

    public function testDefaultRPC(): void
    {
        /** @var RPC $rpc */
        $rpc = $this->makeApp([])->get(RPC::class);
        $this->assertInstanceOf(RPC::class, $rpc);
    }

    /**
     * @expectedException \Spiral\Boot\Exception\BootException
     */
    public function testInvalidRPC(): void
    {
        $this->makeApp([
            'RR_RPC' => 'invalid'
        ])->get(RPC::class);
    }

    /**
     * @expectedException \Spiral\Boot\Exception\BootException
     */
    public function testInvalidRPC2(): void
    {
        $this->makeApp([
            'RR_RPC' => 'ftp://magic'
        ])->get(RPC::class);
    }
}
