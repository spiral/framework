<?php

declare(strict_types=1);

namespace Spiral\Tests\Http;

use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Core\Container;
use Spiral\Http\CallableHandler;
use Spiral\Http\Config\HttpConfig;
use Spiral\Http\Event\RequestHandled;
use Spiral\Http\Event\RequestReceived;
use Spiral\Http\Exception\HttpException;
use Spiral\Http\Http;
use Spiral\Http\Pipeline;
use Spiral\Tests\Http\Diactoros\ResponseFactory;
use Nyholm\Psr7\ServerRequest;

class HttpTest extends TestCase
{
    private $container;

    public function setUp(): void
    {
        $this->container = new Container();
    }

    public function testGetPipeline(): void
    {
        $core = $this->getCore();
        $this->assertInstanceOf(Pipeline::class, $core->getPipeline());
    }

    public function testRunHandler(): void
    {
        $core = $this->getCore();

        $core->setHandler(function () {
            return 'hello world';
        });

        $response = $core->handle(new ServerRequest('GET', ''));
        $this->assertSame('hello world', (string)$response->getBody());
    }

    public function testNoHandler(): void
    {
        $this->expectException(HttpException::class);

        $core = $this->getCore();

        $response = $core->handle(new ServerRequest('GET', ''));
        $this->assertSame('hello world', (string)$response->getBody());
    }

    public function testHandlerInterface(): void
    {
        $core = $this->getCore();
        $core->setHandler(new CallableHandler(function () {
            return 'hello world';
        }, new ResponseFactory(new HttpConfig(['headers' => []]))));

        $response = $core->handle(new ServerRequest('GET', ''));
        $this->assertSame('hello world', (string)$response->getBody());
    }

    public function testDefaultHeaders(): void
    {
        $core = $this->getCore();

        $core->setHandler(function ($req, $resp) {
            return $resp->withAddedHeader('hello', 'value');
        });

        $response = $core->handle(new ServerRequest('GET', ''));
        $this->assertSame(['text/html; charset=UTF-8'], $response->getHeader('Content-Type'));
        $this->assertSame(['value'], $response->getHeader('hello'));
    }

    public function testOutput(): void
    {
        $core = $this->getCore();

        $core->setHandler(function ($req, $resp) {
            echo 'hello!';

            return $resp->withAddedHeader('hello', 'value');
        });

        $response = $core->handle(new ServerRequest('GET', ''));
        $this->assertSame(['text/html; charset=UTF-8'], $response->getHeader('Content-Type'));
        $this->assertSame(['value'], $response->getHeader('hello'));
        $this->assertSame('hello!', (string)$response->getBody());
    }

    public function testOutputAndWrite(): void
    {
        $core = $this->getCore();

        $core->setHandler(function ($req, $resp) {
            echo 'hello!';
            $resp->getBody()->write('world ');

            return $resp->withAddedHeader('hello', 'value');
        });

        $response = $core->handle(new ServerRequest('GET', ''));
        $this->assertSame(['text/html; charset=UTF-8'], $response->getHeader('Content-Type'));
        $this->assertSame(['value'], $response->getHeader('hello'));
        $this->assertSame('world hello!', (string)$response->getBody());
    }

    public function testNestedOutput(): void
    {
        $core = $this->getCore();

        $core->setHandler(function () {
            ob_start();
            ob_start();
            echo 'hello!';
            ob_start();
            ob_start();

            return 'world ';
        });

        $this->assertSame(1, ob_get_level());
        $response = $core->handle(new ServerRequest('GET', ''));
        $this->assertSame(['text/html; charset=UTF-8'], $response->getHeader('Content-Type'));
        $this->assertSame('world hello!', (string)$response->getBody());
        $this->assertSame(1, ob_get_level());
    }

    public function testJson(): void
    {
        $core = $this->getCore();

        $core->setHandler(function () {
            return [
                'status'  => 404,
                'message' => 'not found',
            ];
        });

        $response = $core->handle(new ServerRequest('GET', ''));
        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame(['application/json'], $response->getHeader('Content-Type'));
    }

    public function testJsonSerializable(): void
    {
        $core = $this->getCore();

        $core->setHandler(function () {
            return new Json([
                'status'  => 404,
                'message' => 'not found',
            ]);
        });

        $response = $core->handle(new ServerRequest('GET', ''));
        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame(['application/json'], $response->getHeader('Content-Type'));
    }

    public function testMiddleware(): void
    {
        $core = $this->getCore([HeaderMiddleware::class]);

        $core->setHandler(function () {
            return 'hello?';
        });

        $response = $core->handle(new ServerRequest('GET', ''));
        $this->assertSame(['text/html; charset=UTF-8'], $response->getHeader('Content-Type'));
        $this->assertSame(['Value*'], $response->getHeader('header'));
        $this->assertSame('hello?', (string)$response->getBody());
    }

    public function testMiddlewareTrait(): void
    {
        $core = $this->getCore();

        $core->getPipeline()->pushMiddleware(new Header2Middleware());
        $core->getPipeline()->riseMiddleware(new HeaderMiddleware());

        $core->setHandler(function () {
            return 'hello?';
        });

        $response = $core->handle(new ServerRequest('GET', ''));
        $this->assertSame(['text/html; charset=UTF-8'], $response->getHeader('Content-Type'));
        $this->assertSame(['Value+', 'Value*'], $response->getHeader('header'));
        $this->assertSame('hello?', (string)$response->getBody());
    }

    public function testMiddlewareTraitReversed(): void
    {
        $core = $this->getCore();

        $core->getPipeline()->pushMiddleware(new HeaderMiddleware());
        $core->getPipeline()->riseMiddleware(new Header2Middleware());

        $core->setHandler(function () {
            return 'hello?';
        });

        $response = $core->handle(new ServerRequest('GET', ''));
        $this->assertSame(['text/html; charset=UTF-8'], $response->getHeader('Content-Type'));
        $this->assertSame(['Value*', 'Value+'], $response->getHeader('header'));
        $this->assertSame('hello?', (string)$response->getBody());
    }

    public function testScope(): void
    {
        $core = $this->getCore();

        $core->setHandler(function () {
            $this->assertTrue($this->container->has(ServerRequestInterface::class));

            return 'OK';
        });

        $response = $core->handle(new ServerRequest('GET', ''));
        $this->assertSame('OK', (string)$response->getBody());
    }

    public function testPassException(): void
    {
        $this->expectException(\RuntimeException::class);

        $core = $this->getCore();

        $core->setHandler(function ($req, $resp): void {
            throw new \RuntimeException('error');
        });

        $response = $core->handle(new ServerRequest('GET', ''));
        $this->assertSame(['text/html;charset=UTF-8'], $response->getHeader('Content-Type'));
        $this->assertSame(['value'], $response->getHeader('hello'));
    }

    public function testEventsShouldBeDispatched(): void
    {
        $request = new ServerRequest('GET', '');

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher
            ->expects(self::exactly(2))
            ->method('dispatch')
            ->with($this->callback(static fn (RequestReceived|RequestHandled $event): bool => true));
        $this->container->bind(EventDispatcherInterface::class, $dispatcher);

        $core = $this->getCore();

        $core->setHandler(function () {
            return 'hello world';
        });

        $response = $core->handle($request);
        $this->assertSame('hello world', (string)$response->getBody());
    }

    protected function getCore(array $middleware = []): Http
    {
        $config = new HttpConfig([
            'basePath'   => '/',
            'headers'    => [
                'Content-Type' => 'text/html; charset=UTF-8',
            ],
            'middleware' => $middleware,
        ]);

        return new Http(
            $config,
            new Pipeline($this->container),
            new ResponseFactory($config),
            $this->container
        );
    }
}
