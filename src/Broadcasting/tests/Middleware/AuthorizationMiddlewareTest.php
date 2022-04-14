<?php

declare(strict_types=1);

namespace Spiral\Tests\Broadcasting\Middleware;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Broadcasting\BroadcastInterface;
use Spiral\Broadcasting\Config\BroadcastConfig;
use Spiral\Broadcasting\Middleware\AuthorizationMiddleware;

final class AuthorizationMiddlewareTest extends TestCase
{
    use m\Adapter\Phpunit\MockeryPHPUnitIntegration;

    public function testMiddlewareShouldBeSkippedWhenAuthorizationPathNotSet(): void
    {
        $middleware = new AuthorizationMiddleware(
            m::mock(BroadcastInterface::class),
            new BroadcastConfig([
                'authorize' => [
                    'path' => null,
                    'topics' => [],
                ],
            ]),
            m::mock(ResponseFactoryInterface::class),
        );

        $request = m::mock(ServerRequestInterface::class);
        $handler = m::mock(RequestHandlerInterface::class);

        $request->shouldReceive('getUri')->once()->andReturn($uri = m::mock(UriInterface::class));
        $uri->shouldReceive('getPath')->once()->andReturn('/');

        $handler->shouldReceive('handle')->once()->with($request);

        $middleware->process($request, $handler);
    }

    public function testAuthorizedRequestShouldReturnOkResponse(): void
    {
        $middleware = new AuthorizationMiddleware(
            $broadcast = m::mock(BroadcastInterface::class),
            new BroadcastConfig([
                'authorize' => [
                    'path' => '/auth',
                    'topics' => [],
                ],
            ]),
            $responseFactory = m::mock(ResponseFactoryInterface::class),
        );

        $request = m::mock(ServerRequestInterface::class);
        $handler = m::mock(RequestHandlerInterface::class);

        $request->shouldReceive('getUri')->once()->andReturn($uri = m::mock(UriInterface::class));
        $uri->shouldReceive('getPath')->once()->andReturn('/auth');

        $broadcast->shouldReceive('authorize')->once()->with($request)->andReturn(true);
        $responseFactory->shouldReceive('createResponse')->once()->with(200)->andReturn(m::mock(ResponseInterface::class));

        $middleware->process($request, $handler);
    }

    public function testNotAuthorizedRequestShouldReturn403Response(): void
    {
        $middleware = new AuthorizationMiddleware(
            $broadcast = m::mock(BroadcastInterface::class),
            new BroadcastConfig([
                'authorize' => [
                    'path' => '/auth',
                    'topics' => [],
                ],
            ]),
            $responseFactory = m::mock(ResponseFactoryInterface::class),
        );

        $request = m::mock(ServerRequestInterface::class);
        $handler = m::mock(RequestHandlerInterface::class);

        $request->shouldReceive('getUri')->once()->andReturn($uri = m::mock(UriInterface::class));
        $uri->shouldReceive('getPath')->once()->andReturn('/auth');

        $broadcast->shouldReceive('authorize')->once()->with($request)->andReturn(false);
        $responseFactory->shouldReceive('createResponse')->once()->with(403)->andReturn(m::mock(ResponseInterface::class));

        $middleware->process($request, $handler);
    }
}
