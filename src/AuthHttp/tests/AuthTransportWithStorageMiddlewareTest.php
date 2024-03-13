<?php

declare(strict_types=1);

namespace Spiral\Tests\Auth;

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

final class AuthTransportWithStorageMiddlewareTest extends BaseTestCase
{
    public function testProcessMiddlewareWithTokenStorageProvider(): void
    {
        $storageProvider = $this->createMock(TokenStorageProviderInterface::class);
        $storageProvider
            ->expects($this->once())
            ->method('getStorage')
            ->with('session')
            ->willReturn($tokenStorage = $this->createMock(TokenStorageInterface::class));

        $matcher = $this->exactly(2);
        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->expects($this->exactly(2))
            ->method('withAttribute')
            ->willReturnCallback(function (string $key, string $value) use ($matcher, $tokenStorage) {
                match ($matcher->numberOfInvocations()) {
                    1 =>  $this->assertInstanceOf(AuthContextInterface::class, $value),
                    2 =>  $this->assertSame($tokenStorage, $value),
                };
            })
            ->willReturnSelf();

        $response = $this->createMock(ResponseInterface::class);

        $registry = new TransportRegistry();
        $registry->setTransport('header', $transport = $this->createMock(HttpTransportInterface::class));

        $transport->expects($this->once())->method('fetchToken')->with($request)->willReturn('fooToken');
        $transport->expects($this->once())->method('commitToken')->with($request, $response, '123', null)
            ->willReturn($response);

        $tokenStorage
            ->expects($this->once())
            ->method('load')
            ->with('fooToken')
            ->willReturn($token = $this->createMock(TokenInterface::class));

        $token->expects($this->once())->method('getID')->willReturn('123');
        $token->expects($this->once())->method('getExpiresAt')->willReturn(null);

        $middleware = new AuthTransportWithStorageMiddleware(
            'header',
            $this->createMock(ScopeInterface::class),
            $this->createMock(ActorProviderInterface::class),
            $storageProvider,
            $registry,
            storage: 'session'
        );

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())->method('handle')->with($request)->willReturn($response);

        $this->assertSame(
            $response,
            $middleware->process($request, $handler)
        );
    }
}
