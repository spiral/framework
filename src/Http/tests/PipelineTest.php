<?php

declare(strict_types=1);

namespace Spiral\Tests\Http;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Http\CallableHandler;
use Spiral\Http\Config\HttpConfig;
use Spiral\Http\Event\MiddlewareProcessing;
use Spiral\Http\Exception\PipelineException;
use Spiral\Http\Pipeline;
use Spiral\Telemetry\NullTracer;
use Spiral\Tests\Http\Diactoros\ResponseFactory;
use Nyholm\Psr7\ServerRequest;

final class PipelineTest extends TestCase
{
    public function testTarget(): void
    {
        $pipeline = new Pipeline($this->container, $this->container);

        $handler = new CallableHandler(function () {
            return 'response';
        }, new ResponseFactory(new HttpConfig(['headers' => []])));

        $response = $pipeline->withHandler($handler)->handle(new ServerRequest('GET', ''));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('OK', $response->getReasonPhrase());
        $this->assertSame('response', (string)$response->getBody());
    }

    public function testHandle(): void
    {
        $pipeline = new Pipeline($this->container, $this->container);

        $handler = new CallableHandler(function () {
            return 'response';
        }, new ResponseFactory(new HttpConfig(['headers' => []])));

        $response = $pipeline->process(new ServerRequest('GET', ''), $handler);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('OK', $response->getReasonPhrase());
        $this->assertSame('response', (string)$response->getBody());
    }

    public function testHandleException(): void
    {
        $this->expectException(PipelineException::class);

        $pipeline = new Pipeline($this->container, $this->container);
        $pipeline->handle(new ServerRequest('GET', ''));
    }

    public function testMiddlewareProcessingEventShouldBeDispatched(): void
    {
        $middleware = new class implements MiddlewareInterface {
            public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
            {
                return (new ResponseFactory(new HttpConfig(['headers' => []])))->createResponse(200);
            }
        };
        $request = new ServerRequest('GET', '');
        $handler = new CallableHandler(function () {
            return 'response';
        }, new ResponseFactory(new HttpConfig(['headers' => []])));

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher
            ->expects(self::once())
            ->method('dispatch')
            ->with(new MiddlewareProcessing($request, $middleware));

        $pipeline = new Pipeline($this->container, $this->container, $dispatcher, new NullTracer($this->container));

        $pipeline->pushMiddleware($middleware);

        $pipeline->withHandler($handler)->handle($request);
    }
}
