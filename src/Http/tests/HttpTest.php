<?php

declare(strict_types=1);

namespace Spiral\Tests\Http;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Spiral\Core\Container;
use Spiral\Core\Options;
use Spiral\Http\CallableHandler;
use Spiral\Http\Config\HttpConfig;
use Spiral\Http\Event\RequestHandled;
use Spiral\Http\Event\RequestReceived;
use Spiral\Http\Exception\HttpException;
use Spiral\Http\Http;
use Spiral\Http\Pipeline;
use Spiral\Telemetry\NullTracer;
use Spiral\Telemetry\TracerFactoryInterface;
use Spiral\Telemetry\TracerInterface;
use Spiral\Telemetry\TraceKind;
use Spiral\Tests\Http\Diactoros\ResponseFactory;
use Nyholm\Psr7\ServerRequest;

final class HttpTest extends TestCase
{
    use m\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private Container $container;

    public function testGetPipeline(): void
    {
        $core = $this->getCore();
        self::assertInstanceOf(Pipeline::class, $core->getPipeline());
    }

    public function testRunHandler(): void
    {
        $core = $this->getCore();

        $core->setHandler(static fn(): string => 'hello world');

        $response = $core->handle(new ServerRequest('GET', ''));
        self::assertSame('hello world', (string) $response->getBody());
    }

    public function testNoHandler(): void
    {
        $this->expectException(HttpException::class);

        $core = $this->getCore();

        $response = $core->handle(new ServerRequest('GET', ''));
        self::assertSame('hello world', (string) $response->getBody());
    }

    public function testHandlerInterface(): void
    {
        $core = $this->getCore();
        $core->setHandler(
            new CallableHandler(static fn(): string => 'hello world', new ResponseFactory(new HttpConfig(['headers' => []]))),
        );

        $response = $core->handle(new ServerRequest('GET', ''));
        self::assertSame('hello world', (string) $response->getBody());
    }

    public function testDefaultHeaders(): void
    {
        $core = $this->getCore();

        $core->setHandler(static fn($req, $resp) => $resp->withAddedHeader('hello', 'value'));

        $response = $core->handle(new ServerRequest('GET', ''));
        self::assertSame(['text/html; charset=UTF-8'], $response->getHeader('Content-Type'));
        self::assertSame(['value'], $response->getHeader('hello'));
    }

    public function testOutput(): void
    {
        $core = $this->getCore();

        $core->setHandler(static function ($req, $resp) {
            echo 'hello!';

            return $resp->withAddedHeader('hello', 'value');
        });

        $response = $core->handle(new ServerRequest('GET', ''));
        self::assertSame(['text/html; charset=UTF-8'], $response->getHeader('Content-Type'));
        self::assertSame(['value'], $response->getHeader('hello'));
        self::assertSame('hello!', (string) $response->getBody());
    }

    public function testOutputAndWrite(): void
    {
        $core = $this->getCore();

        $core->setHandler(static function ($req, $resp) {
            echo 'hello!';
            $resp->getBody()->write('world ');

            return $resp->withAddedHeader('hello', 'value');
        });

        $response = $core->handle(new ServerRequest('GET', ''));
        self::assertSame(['text/html; charset=UTF-8'], $response->getHeader('Content-Type'));
        self::assertSame(['value'], $response->getHeader('hello'));
        self::assertSame('world hello!', (string) $response->getBody());
    }

    public function testNestedOutput(): void
    {
        $core = $this->getCore();

        $core->setHandler(static function (): string {
            \ob_start();
            \ob_start();
            echo 'hello!';
            \ob_start();
            \ob_start();

            return 'world ';
        });

        self::assertSame(1, \ob_get_level());
        $response = $core->handle(new ServerRequest('GET', ''));
        self::assertSame(['text/html; charset=UTF-8'], $response->getHeader('Content-Type'));
        self::assertSame('world hello!', (string) $response->getBody());
        self::assertSame(1, \ob_get_level());
    }

    public function testJson(): void
    {
        $core = $this->getCore();

        $core->setHandler(static fn(): array => [
            'status' => 404,
            'message' => 'not found',
        ]);

        $response = $core->handle(new ServerRequest('GET', ''));
        self::assertSame(404, $response->getStatusCode());
        self::assertSame(['application/json'], $response->getHeader('Content-Type'));
    }

    public function testJsonSerializable(): void
    {
        $core = $this->getCore();

        $core->setHandler(static fn(): Json => new Json([
            'status' => 404,
            'message' => 'not found',
        ]));

        $response = $core->handle(new ServerRequest('GET', ''));
        self::assertSame(404, $response->getStatusCode());
        self::assertSame(['application/json'], $response->getHeader('Content-Type'));
    }

    public function testMiddleware(): void
    {
        $core = $this->getCore([HeaderMiddleware::class]);

        $core->setHandler(static fn(): string => 'hello?');

        $response = $core->handle(new ServerRequest('GET', ''));
        self::assertSame(['text/html; charset=UTF-8'], $response->getHeader('Content-Type'));
        self::assertSame(['Value*'], $response->getHeader('header'));
        self::assertSame('hello?', (string) $response->getBody());
    }

    public function testMiddlewareTrait(): void
    {
        $core = $this->getCore();

        $core->getPipeline()->pushMiddleware(new Header2Middleware());
        $core->getPipeline()->riseMiddleware(new HeaderMiddleware());

        $core->setHandler(static fn(): string => 'hello?');

        $response = $core->handle(new ServerRequest('GET', ''));
        self::assertSame(['text/html; charset=UTF-8'], $response->getHeader('Content-Type'));
        self::assertSame(['Value+', 'Value*'], $response->getHeader('header'));
        self::assertSame('hello?', (string) $response->getBody());
    }

    public function testMiddlewareTraitReversed(): void
    {
        $core = $this->getCore();

        $core->getPipeline()->pushMiddleware(new HeaderMiddleware());
        $core->getPipeline()->riseMiddleware(new Header2Middleware());

        $core->setHandler(static fn(): string => 'hello?');

        $response = $core->handle(new ServerRequest('GET', ''));
        self::assertSame(['text/html; charset=UTF-8'], $response->getHeader('Content-Type'));
        self::assertSame(['Value*', 'Value+'], $response->getHeader('header'));
        self::assertSame('hello?', (string) $response->getBody());
    }

    public function testPassException(): void
    {
        $this->expectException(\RuntimeException::class);

        $core = $this->getCore();

        $core->setHandler(static function ($req, $resp): never {
            throw new \RuntimeException('error');
        });

        $response = $core->handle(new ServerRequest('GET', ''));
        self::assertSame(['text/html;charset=UTF-8'], $response->getHeader('Content-Type'));
        self::assertSame(['value'], $response->getHeader('hello'));
    }

    public function testEventsShouldBeDispatched(): void
    {
        $request = new ServerRequest('GET', '');

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher
            ->expects(self::exactly(2))
            ->method('dispatch')
            ->with($this->callback(static fn(RequestReceived|RequestHandled $event): bool => true));
        $this->container->bind(EventDispatcherInterface::class, $dispatcher);

        $core = $this->getCore();

        $core->setHandler(static fn(): string => 'hello world');

        $response = $core->handle($request);
        self::assertSame('hello world', (string) $response->getBody());
    }

    public function testPassingTracerIntoScope(): void
    {
        $config = $this->getHttpConfig();
        $request = new ServerRequest('GET', 'http://example.org/path', ['foo' => ['bar']]);

        $http = new Http(
            $config,
            new Pipeline($this->container),
            new ResponseFactory($config),
            $this->container,
            $tracerFactory = m::mock(TracerFactoryInterface::class),
        );

        $http->setHandler(static fn(): string => 'hello world');

        $tracerFactory
            ->shouldReceive('make')
            ->once()
            ->with(['Host' => ['example.org'], 'foo' => ['bar']])
            ->andReturn(new NullTracer($this->container));

        $response = $http->handle($request);
        self::assertSame('hello world', (string) $response->getBody());
    }

    public function testTraceAttributesAreSet(): void
    {
        $config = $this->getHttpConfig();
        $request = new ServerRequest('GET', 'http://example.org/path', ['foo' => ['bar']]);

        $tracer = $this->createMock(TracerInterface::class);
        $tracer
            ->expects($this->once())
            ->method('trace')
            ->with(
                'GET http://example.org/path',
                $this->anything(),
                [
                    'http.method' => 'GET',
                    'http.url' => 'http://example.org/path',
                    'http.headers' => ['Host' => 'example.org', 'foo' => 'bar'],
                ],
                true,
                TraceKind::SERVER,
            )
            ->willReturnCallback(
                function ($name, $callback, $attributes, $scoped, $traceKind) {
                    self::assertSame($attributes, [
                        'http.method' => 'GET',
                        'http.url' => 'http://example.org/path',
                        'http.headers' => ['Host' => 'example.org', 'foo' => 'bar'],
                    ]);
                    return $this->container
                        ->get(TracerInterface::class)
                        ->trace($name, $callback, $attributes, $scoped, $traceKind);
                },
            );
        $tracer
            ->expects($this->once())
            ->method('getContext')
            ->willReturn([]);

        $tracerFactory = $this->createMock(TracerFactoryInterface::class);
        $tracerFactory
            ->expects($this->once())
            ->method('make')
            ->willReturn($tracer);

        $http = new Http(
            $config,
            new Pipeline($this->container),
            new ResponseFactory($config),
            $this->container,
            $tracerFactory,
        );

        $http->setHandler(static fn(): string => 'hello world');

        $response = $http->handle($request);
        self::assertSame('hello world', (string) $response->getBody());
    }

    public function testTraceContextIsAppliedToResponse(): void
    {
        $config = $this->getHttpConfig();
        $request = new ServerRequest('GET', '', ['foo' => ['bar']]);

        $http = new Http(
            $config,
            new Pipeline($this->container),
            new ResponseFactory($config),
            $this->container,
            $tracerFactory = m::mock(TracerFactoryInterface::class),
        );

        $http->setHandler(static fn(): string => 'hello world');

        $tracerFactory
            ->shouldReceive('make')
            ->once()
            ->andReturn($tracer = m::mock(TracerInterface::class));

        $tracer
            ->shouldReceive('trace')
            ->once()
            ->andReturnUsing(
                fn($name, $callback, $attributes, $scoped, $traceKind) => $this
                    ->container
                    ->get(TracerInterface::class)
                    ->trace($name, $callback, $attributes, $scoped, $traceKind),
            );

        $tracer
            ->shouldReceive('getContext')
            ->once()
            ->withNoArgs()
            ->andReturn(['baz' => 'quux']);

        $response = $http->handle($request);
        self::assertSame('hello world', (string) $response->getBody());
        self::assertSame(['quux'], $response->getHeader('baz'));
    }

    public function getHttpConfig(array $middleware = []): HttpConfig
    {
        return new HttpConfig([
            'basePath' => '/',
            'headers' => [
                'Content-Type' => 'text/html; charset=UTF-8',
            ],
            'middleware' => $middleware,
        ]);
    }

    protected function setUp(): void
    {
        $options = new Options();
        $options->checkScope = false;
        $this->container = new Container(options: $options);
        $this->container->bind(TracerInterface::class, new NullTracer($this->container));
    }

    protected function getCore(array $middleware = []): Http
    {
        $config = $this->getHttpConfig($middleware);

        return new Http(
            $config,
            new Pipeline($this->container),
            new ResponseFactory($config),
            $this->container,
            dispatcher: $this->container->has(EventDispatcherInterface::class)
                ? $this->container->get(EventDispatcherInterface::class)
                : null,
        );
    }
}
