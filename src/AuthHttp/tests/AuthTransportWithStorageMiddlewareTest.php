<?php

declare(strict_types=1);

namespace Spiral\Tests\Auth;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Auth\ActorProviderInterface;
use Spiral\Auth\AuthContextInterface;
use Spiral\Auth\HttpTransportInterface;
use Spiral\Auth\Middleware\AuthTransportWithStorageMiddleware;
use Spiral\Auth\TokenInterface;
use Spiral\Auth\TokenStorageInterface;
use Spiral\Auth\TokenStorageProviderInterface;
use Spiral\Auth\TransportRegistry;
use Spiral\Core\ScopeInterface;

final class AuthTransportWithStorageMiddlewareTest extends TestCase
{
    public function testProcessMiddlewareWithTokenStorageProvider(): void
    {
        $serverRequest = m::mock(ServerRequestInterface::class);
        $storageProvider = m::mock(TokenStorageProviderInterface::class);
        $scope = m::mock(ScopeInterface::class);
        $response = m::mock(ResponseInterface::class);

        $storageProvider->shouldReceive('getStorage')->once()->with('session')->andReturn(
            $tokenStorage = m::mock(TokenStorageInterface::class)
        );

        $registry = new TransportRegistry();
        $registry->setTransport('header', $transport = m::mock(HttpTransportInterface::class));

        $transport->shouldReceive('fetchToken')->once()->with($serverRequest)->andReturn('fooToken');
        $transport->shouldReceive('commitToken')->once()->with($serverRequest, $response, '123', null)
            ->andReturn($response);

        $tokenStorage->shouldReceive('load')->once()->with('fooToken')->andReturn(
            $token = m::mock(TokenInterface::class)
        );

        $scope
            ->shouldReceive('runScope')
            ->once()
            ->withArgs(
                fn(array $bindings, callable $callback) => $bindings[AuthContextInterface::class]
                        ->getToken() instanceof $token
            )
            ->andReturn($response);

        $token->shouldReceive('getID')->once()->andReturn('123');
        $token->shouldReceive('getExpiresAt')->once()->andReturnNull();

        $middleware = new AuthTransportWithStorageMiddleware(
            'header',
            $scope,
            m::mock(ActorProviderInterface::class),
            $storageProvider,
            $registry,
            storage: 'session'
        );

        $this->assertSame(
            $response,
            $middleware->process($serverRequest, m::mock(RequestHandlerInterface::class))
        );
    }
}
