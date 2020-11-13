<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Framework\GRPC;

use Mockery as m;
use Spiral\Files\Files;
use Spiral\GRPC\GRPCDispatcher;
use Spiral\RoadRunner\Worker;
use Spiral\App\Service\Sub\Message;
use Spiral\Tests\Framework\ConsoleTest;

class DispatcherTest extends ConsoleTest
{
    public function setUp(): void
    {
        exec('protoc 2>&1', $out);
        if (strpos(implode("\n", $out), '--php_out') === false) {
            $this->markTestSkipped('Protoc binary is missing');
        }

        parent::setUp();

        $fs = new Files();

        $this->runCommandDebug('grpc:generate', [
            'proto' => realpath($fs->normalizePath($this->app->dir('app') . 'proto/service.proto'))
        ]);

        $output = $this->app->dir('app') . 'src/Service/EchoService.php';
        file_put_contents($output, GenerateTest::SERVICE);
    }

    public function tearDown(): void
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

    public function testCanServe(): void
    {
        $this->assertFalse($this->app->get(GRPCDispatcher::class)->canServe());
    }

    public function testCanServe2(): void
    {
        $this->app->getEnvironment()->set('RR_GRPC', true);
        $this->assertTrue($this->app->get(GRPCDispatcher::class)->canServe());
    }

    /**
     * WARNING: spiral/php-grpc not compatible with PHP 8.0
     *
     * @requires PHP <= 7.4
     */
    public function testServe(): void
    {
        $w = m::mock(Worker::class);

        $this->app->getEnvironment()->set('RR_GRPC', true);
        $this->app->getContainer()->bind(Worker::class, $w);

        $msg = new Message();
        $msg->setMsg('hello');

        $w->shouldReceive('receive')->once()->with(
            \Mockery::on(function (&$context) {
                $context = '{
                  "service": "service.Echo",
                  "method": "Ping",
                  "context": {}
                }';

                return true;
            })
        )->andReturn($msg->serializeToString());

        $w->shouldReceive('send')->once()->with(
            \Mockery::on(function ($out) {
                $msg = new Message();
                $msg->mergeFromString($out);
                $this->assertSame('hello', $msg->getMsg());

                return true;
            }),
            '{}'
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

    public function testError(): void
    {
        $w = m::mock(Worker::class);

        $this->app->getEnvironment()->set('RR_GRPC', true);
        $this->app->getContainer()->bind(Worker::class, $w);

        $msg = new Message();
        $msg->setMsg('hello');

        $w->shouldReceive('receive')->once()->with(
            \Mockery::on(function (&$context) {
                $context = '{
                  "service": "service.Echo",
                  "method": "Invalid",
                  "context": {}
                }';

                return true;
            })
        )->andReturn($msg->serializeToString());

        $w->shouldReceive('error')->once()->with(
            \Mockery::on(function ($out) {
                $this->assertStringContainsString('Method `Invalid` not found', $out);
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
