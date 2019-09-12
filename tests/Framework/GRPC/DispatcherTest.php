<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Framework\GRPC;

use Mockery as m;
use Spiral\App\Service\Sub\Message;
use Spiral\Files\Files;
use Spiral\Framework\ConsoleTest;
use Spiral\GRPC\GRPCDispatcher;
use Spiral\RoadRunner\Worker;

class DispatcherTest extends ConsoleTest
{
    public function setUp()
    {
        exec('protoc 2>&1', $out);
        if (strpos(join("\n", $out), '--php_out') === false) {
            $this->markTestSkipped('Protoc binary is missing');
        }

        parent::setUp();

        $fs = new Files();
        $proto = $fs->normalizePath($this->app->dir('app') . 'proto/service.proto');

        // protoc can't figure relative paths
        $proto = str_replace('Framework/../', '', $proto);

        $this->runCommandDebug('grpc:generate', [
            'proto' => $proto
        ]);

        file_put_contents($this->app->dir('app') . 'src/Service/EchoService.php', GenerateTest::SERVICE);
    }

    public function tearDown()
    {
        parent::tearDown();

        $fs = new Files();

        if ($fs->isDirectory($this->app->dir('app') . 'src/Service')) {
            $fs->deleteDirectory($this->app->dir('app') . 'src/Service');
        }

        if ($fs->isDirectory($this->app->dir('app') . 'src/GPBMetadata')) {
            $fs->deleteDirectory($this->app->dir('app') . 'src/GPBMetadata');
        }
    }

    public function testCanServe()
    {
        $this->assertFalse($this->app->get(GRPCDispatcher::class)->canServe());
    }

    public function testCanServe2()
    {
        $this->app->getEnvironment()->set('RR_GRPC', true);
        $this->assertTrue($this->app->get(GRPCDispatcher::class)->canServe());
    }

    public function testServe()
    {
        $w = m::mock(Worker::class);

        $this->app->getEnvironment()->set('RR_GRPC', true);
        $this->app->getContainer()->bind(Worker::class, $w);

        $msg = new Message();
        $msg->setMsg("hello");

        $w->shouldReceive('receive')->once()->with(
            \Mockery::on(function (&$context) {
                $context = '{
                  "service": "service.Echo",
                  "method": "Ping"               
                }';

                return true;
            })
        )->andReturn($msg->serializeToString());

        $w->shouldReceive('send')->once()->with(
            \Mockery::on(function ($out) {
                $msg = new Message();
                $msg->mergeFromString($out);
                $this->assertSame("hello", $msg->getMsg());

                return true;
            })
        )->andReturn(true);

        // one command only
        $w->shouldReceive('receive')->once()->with(
            \Mockery::on(function (&$context) {
                $context = null;
                return true;
            })
        )->andReturn(null);

        $this->app->get(GRPCDispatcher::class)->serve();
    }

    public function testError()
    {
        $w = m::mock(Worker::class);

        $this->app->getEnvironment()->set('RR_GRPC', true);
        $this->app->getContainer()->bind(Worker::class, $w);

        $msg = new Message();
        $msg->setMsg("hello");

        $w->shouldReceive('receive')->once()->with(
            \Mockery::on(function (&$context) {
                $context = '{
                  "service": "service.Echo",
                  "method": "Invalid"               
                }';

                return true;
            })
        )->andReturn($msg->serializeToString());

        $w->shouldReceive('error')->once()->with(
            \Mockery::on(function ($out) {
                $this->assertContains('Method `Invalid` not found', $out);
                return true;
            })
        )->andReturn(true);

        // one command only
        $w->shouldReceive('receive')->once()->with(
            \Mockery::on(function (&$context) {
                $context = null;
                return true;
            })
        )->andReturn(null);

        $this->app->get(GRPCDispatcher::class)->serve();
    }
}
