<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Framework;

use Spiral\App\TestApp;
use Spiral\Boot\Environment;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Goridge\RPC;

class KernelTest extends BaseTest
{
    public function testBypassEnvironmentToConfig()
    {
        $configs = $this->makeApp([
            'TEST_VALUE' => 'HELLO WORLD'
        ])->get(ConfiguratorInterface::class);

        $this->assertSame([
            'key' => 'HELLO WORLD'
        ], $configs->getConfig('test'));
    }

    public function testIsDebug()
    {
        $app = $this->makeApp([
            'DEBUG' => true
        ]);

        $this->assertTrue($app->isDebug());
    }

    public function testGetEnv()
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
    public function testNoRootDirectory()
    {
        TestApp::init([
        ], new Environment(), false);
    }

    public function testDefaultRPC()
    {
        /** @var RPC $rpc */
        $rpc = $this->makeApp([])->get(RPC::class);
        $this->assertInstanceOf(RPC::class, $rpc);
    }

    /**
     * @expectedException \Spiral\Boot\Exception\BootException
     */
    public function testInvalidRPC()
    {
        $this->makeApp([
            'RR_RPC' => 'invalid'
        ])->get(RPC::class);
    }

    /**
     * @expectedException \Spiral\Boot\Exception\BootException
     */
    public function testInvalidRPC2()
    {
        $this->makeApp([
            'RR_RPC' => 'ftp://magic'
        ])->get(RPC::class);
    }
}