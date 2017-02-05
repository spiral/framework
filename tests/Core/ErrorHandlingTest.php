<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Core;

use Psr\Log\LoggerInterface;
use Spiral\Core\DispatcherInterface;
use Spiral\Debug\SnapshotInterface;
use Spiral\Tests\BaseTest;
use Mockery as m;

class ErrorHandlingTest extends BaseTest
{
    public function testLogError()
    {
        $log = $this->app->container->get(LoggerInterface::class)->alert('error');
    }

    public function testConvertException()
    {
        try {
            $e = $this->app->handleError(10, 'error', __FILE__, 17);
        } catch (\ErrorException $e) {
        }

        $this->assertInstanceOf(\ErrorException::class, $e);
        $this->assertSame(10, $e->getCode());
        $this->assertSame('error', $e->getMessage());
        $this->assertSame(__FILE__, $e->getFile());
        $this->assertSame(17, $e->getLine());
    }

    public function testLogError2()
    {
        $this->app->container->get(LoggerInterface::class)->alert('error');
    }

    public function testMakeSnapshot()
    {
        $snapshot = $this->app->makeSnapshot(new \ErrorException('exception'));
        $this->assertSame('exception', $snapshot->getException()->getMessage());
    }

    public function testHandleSnapshot()
    {
        $dispatcher = m::mock(DispatcherInterface::class);
        $dispatcher->shouldReceive('start')->andReturnNull();

        $this->app->start($dispatcher);

        $dispatcher->shouldReceive('handleSnapshot')->with(\Mockery::on(function ($arg) {
            return $arg instanceof SnapshotInterface && $arg->getException()->getMessage() == 'exception';
        }))->andReturnNull();

        $this->app->handleException(new \Error('exception'));
    }

    public function testLogError3()
    {
        $this->app->container->get(LoggerInterface::class)->alert('error');
    }

    /**
     * @expectedException \Error
     */
    public function testHandleExceptionWhenNoSnapshot()
    {
        $this->container->removeBinding(SnapshotInterface::class);
        $this->app->handleException(new \Error('exception'));
    }

    public function testHandleSnapshotNoDispatcher()
    {
        ob_start();
        $this->app->handleException(new \Error('exception'));

        $this->assertNotEmpty(ob_get_clean());
    }
}