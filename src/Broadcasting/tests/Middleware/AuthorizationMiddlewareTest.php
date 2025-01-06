<?php

declare(strict_types=1);

namespace Spiral\Tests\Broadcasting\Middleware;

use Mockery as m;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Broadcasting\AuthorizationStatus;
use Spiral\Broadcasting\BroadcastInterface;
use Spiral\Broadcasting\Event\Authorized;
use Spiral\Broadcasting\Event\AuthorizationSuccess;
use Spiral\Broadcasting\GuardInterface;
use Spiral\Broadcasting\Middleware\AuthorizationMiddleware;

final class AuthorizationMiddlewareTest extends TestCase
{
    use m\Adapter\Phpunit\MockeryPHPUnitIntegration;

    public function testMiddlewareShouldBeSkippedWhenAuthorizationPathNotSet(): void
    {
        $middleware = new AuthorizationMiddleware(
            m::mock(BroadcastInterface::class),
            m::mock(ResponseFactoryInterface::class),
            null
        );

        $request = m::mock(ServerRequestInterface::class);
        $handler = m::mock(RequestHandlerInterface::class);

        $request->shouldReceive('getUri')->once()->andReturn($uri = m::mock(UriInterface::class));
        $uri->shouldReceive('getPath')->once()->andReturn('/');

        $handler->shouldReceive('handle')->once()->with($request);

        $middleware->process($request, $handler);
    }

    public function testNotGuardedBroadcastShouldReturnOkResponse(): void
    {
        $middleware = new AuthorizationMiddleware(
            $broadcast = m::mock(BroadcastInterface::class),
            $responseFactory = m::mock(ResponseFactoryInterface::class),
            '/auth',
        );

        $request = m::mock(ServerRequestInterface::class);
        $handler = m::mock(RequestHandlerInterface::class);

        $request->shouldReceive('getUri')->once()->andReturn($uri = m::mock(UriInterface::class));
        $uri->shouldReceive('getPath')->once()->andReturn('/auth');

        $responseFactory->shouldReceive('createResponse')->once()->with(200)->andReturn(
            m::mock(ResponseInterface::class)
        );

        $middleware->process($request, $handler);
    }

    public function testGuardedBroadcastWithAuthorizedRequestShouldReturnOkResponse(): void
    {
        $middleware = new AuthorizationMiddleware(
            $broadcast = m::mock(BroadcastInterface::class, GuardInterface::class),
            $responseFactory = m::mock(ResponseFactoryInterface::class),
            '/auth',
        );

        $request = m::mock(ServerRequestInterface::class);
        $handler = m::mock(RequestHandlerInterface::class);

        $request->shouldReceive('getUri')->once()->andReturn($uri = m::mock(UriInterface::class));
        $uri->shouldReceive('getPath')->once()->andReturn('/auth');

        $broadcast->shouldReceive('authorize')->once()->with($request)
            ->andReturn(
                new AuthorizationStatus(
                    true, ['topic_name'], ['foo' => 'bar']
                )
            );

        $responseFactory->shouldReceive('createResponse')->once()->with(200)->andReturn(
            m::mock(ResponseInterface::class)
        );

        $middleware->process($request, $handler);
    }

    public function testGuardedBroadcastWithNotAuthorizedRequestShouldReturnForbidResponse(): void
    {
        $middleware = new AuthorizationMiddleware(
            $broadcast = m::mock(BroadcastInterface::class, GuardInterface::class),
            $responseFactory = m::mock(ResponseFactoryInterface::class),
            '/auth',
        );

        $request = m::mock(ServerRequestInterface::class);
        $handler = m::mock(RequestHandlerInterface::class);

        $request->shouldReceive('getUri')->once()->andReturn($uri = m::mock(UriInterface::class));
        $uri->shouldReceive('getPath')->once()->andReturn('/auth');

        $broadcast->shouldReceive('authorize')->once()->with($request)
            ->andReturn(
                new AuthorizationStatus(
                    false, ['topic_name'], ['foo' => 'bar']
                )
            );

        $responseFactory->shouldReceive('createResponse')->once()->with(403)->andReturn(
            m::mock(ResponseInterface::class)
        );

        $middleware->process($request, $handler);
    }

    public function testGuardedBroadcastWithCustomResponseShouldReturnIt(): void
    {
        $middleware = new AuthorizationMiddleware(
            $broadcast = m::mock(BroadcastInterface::class, GuardInterface::class),
            $responseFactory = m::mock(ResponseFactoryInterface::class),
            '/auth',
        );

        $request = m::mock(ServerRequestInterface::class);
        $handler = m::mock(RequestHandlerInterface::class);

        $request->shouldReceive('getUri')->once()->andReturn($uri = m::mock(UriInterface::class));
        $uri->shouldReceive('getPath')->once()->andReturn('/auth');

        $broadcast->shouldReceive('authorize')->once()->with($request)
            ->andReturn(
                new AuthorizationStatus(
                    false, ['topic_name'], ['foo' => 'bar'], $response = m::mock(ResponseInterface::class)
                )
            );

        self::assertSame($response, $middleware->process($request, $handler));
    }

    #[DataProvider('eventsDataProvider')]
    public function testAuthorizationEventsShouldBeDispatched(string $event, bool $authStatus, int $code): void
    {
        $request = m::mock(ServerRequestInterface::class);
        $handler = m::mock(RequestHandlerInterface::class);
        $dispatcher = m::mock(EventDispatcherInterface::class);
        $status = new AuthorizationStatus($authStatus, ['topic_name'], ['foo' => 'bar']);
        $dispatcher->shouldReceive('dispatch')
            ->once()
            ->withArgs(static fn(Authorized $e): bool => $e->status === $status && $e::class === $event);

        $middleware = new AuthorizationMiddleware(
            $broadcast = m::mock(BroadcastInterface::class, GuardInterface::class),
            $responseFactory = m::mock(ResponseFactoryInterface::class),
            '/auth',
            $dispatcher
        );

        $request->shouldReceive('getUri')
            ->once()->andReturn($uri = m::mock(UriInterface::class));
        $uri->shouldReceive('getPath')
            ->once()->andReturn('/auth');
        $broadcast->shouldReceive('authorize')
            ->once()->with($request)->andReturn($status);
        $responseFactory->shouldReceive('createResponse')
            ->once()->with($code)->andReturn(m::mock(ResponseInterface::class));

        $middleware->process($request, $handler);
    }

    public function testAuthorizationEventsWithoutGuardShouldBeDispatched(): void
    {
        $request = m::mock(ServerRequestInterface::class);
        $handler = m::mock(RequestHandlerInterface::class);
        $dispatcher = m::mock(EventDispatcherInterface::class);
        $dispatcher->shouldReceive('dispatch')
            ->once()
            ->withArgs(
                static fn(Authorized $event): bool => $event->status->success === true
                && $event->status->topics === null
                && !$event->status->response instanceof \Psr\Http\Message\ResponseInterface
            );

        $middleware = new AuthorizationMiddleware(
            m::mock(BroadcastInterface::class),
            $responseFactory = m::mock(ResponseFactoryInterface::class),
            '/auth',
            $dispatcher
        );

        $request->shouldReceive('getUri')->once()->andReturn($uri = m::mock(UriInterface::class));
        $uri->shouldReceive('getPath')->once()->andReturn('/auth');
        $responseFactory->shouldReceive('createResponse')
            ->once()->with(200)->andReturn(m::mock(ResponseInterface::class));

        $middleware->process($request, $handler);
    }

    public static function eventsDataProvider(): \Traversable
    {
        yield [Authorized::class, true, 200];
        yield [Authorized::class, false, 403];
    }
}
