<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Http;

use Mockery as m;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Debug\SnapshotInterface;
use Spiral\Http\Configs\HttpConfig;
use Zend\Diactoros\Response\EmitterInterface;

class HttpDispatcherTest extends HttpTest
{
    public function testStartAndEmit()
    {
        $emitter = new class implements EmitterInterface
        {
            public function emit(ResponseInterface $response, $maxBufferLevel = null)
            {
                HttpCoreTest::assertEquals(300, $response->getStatusCode());
                HttpCoreTest::assertEquals(ob_get_level(), $maxBufferLevel);
            }
        };

        $this->http->setEmitter($emitter);
        $this->http->setEndpoint(function (
            ServerRequestInterface $request,
            ResponseInterface $response
        ) {
            return $response->withStatus(300);
        });

        $this->http->start();
    }

    public function testSnapshotHandler()
    {
        $snapshot = $this->container->make(SnapshotInterface::class, [
            'exception' => new \Error('error')
        ]);

        $emitter = new class implements EmitterInterface
        {
            public function emit(ResponseInterface $response, $maxBufferLevel = null)
            {
                HttpCoreTest::assertEquals(500, $response->getStatusCode());
                HttpCoreTest::assertEquals($this->snapshot->render(),
                    $response->getBody()->__toString());
            }
        };

        $emitter->snapshot = $snapshot;

        $this->http->setEmitter($emitter);
        $this->http->handleSnapshot($snapshot);
    }

    public function testSnapshotHandlerExposeErrorsTrue()
    {
        $config = $this->container->get(HttpConfig::class);
        $mock = m::mock($config);

        $mock->shouldReceive('exposeErrors')->andReturn(true);

        $this->container->bind(HttpConfig::class, $mock);

        $snapshot = $this->container->make(SnapshotInterface::class, [
            'exception' => new \Error('error')
        ]);

        $emitter = new class implements EmitterInterface
        {
            public function emit(ResponseInterface $response, $maxBufferLevel = null)
            {
                HttpCoreTest::assertEquals(500, $response->getStatusCode());
                HttpCoreTest::assertEquals($this->snapshot->render(),
                    $response->getBody()->__toString());
            }
        };

        $emitter->snapshot = $snapshot;

        $this->http->setEmitter($emitter);
        $this->http->handleSnapshot($snapshot);
    }

    public function testSnapshotHandlerExposeErrorsFalse()
    {
        $config = $this->container->get(HttpConfig::class);
        $mock = m::mock($config);

        $mock->shouldReceive('exposeErrors')->andReturn(false);

        $this->container->bind(HttpConfig::class, $mock);

        $snapshot = $this->container->make(SnapshotInterface::class, [
            'exception' => new \Error('error')
        ]);

        $emitter = new class implements EmitterInterface
        {
            public function emit(ResponseInterface $response, $maxBufferLevel = null)
            {
                HttpCoreTest::assertEquals(500, $response->getStatusCode());
                HttpCoreTest::assertNotContains(
                    $this->snapshot->getMessage(),
                    $response->getBody()->__toString()
                );
            }
        };

        $emitter->snapshot = $snapshot;

        $this->http->setEmitter($emitter);
        $this->http->handleSnapshot($snapshot);
    }
}