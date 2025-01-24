<?php

declare(strict_types=1);

namespace Spiral\Tests\Auth\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Auth\ActorProviderInterface;
use Spiral\Auth\AuthContextInterface;
use Spiral\Auth\Middleware\AuthTransportMiddleware;
use Spiral\Auth\TokenStorageInterface;
use Spiral\Auth\Transport\CookieTransport;
use Spiral\Auth\Transport\HeaderTransport;
use Spiral\Auth\TransportRegistry;
use Spiral\Core\Container\Autowire;
use Spiral\Core\ScopeInterface;
use Spiral\Testing\Http\Token;
use Spiral\Tests\Auth\BaseTestCase;
use Spiral\Tests\Auth\Stub\TestAuthHttpProvider;
use Spiral\Tests\Auth\Stub\TestAuthHttpStorage;

final class AuthTransportMiddlewareTest extends BaseTestCase
{
    public function testCreateMiddlewareWithOneTransport(): void
    {
        $middleware1 = new Autowire(AuthTransportMiddleware::class, ['cookie']);
        $middleware2 = new Autowire(AuthTransportMiddleware::class, ['header']);

        $auth = $this->getPrivateProperty('authMiddleware', $middleware1->resolve($this->container));
        $auth2 = $this->getPrivateProperty('authMiddleware', $middleware2->resolve($this->container));

        /** @var TransportRegistry $registry */
        $registry = $this->getPrivateProperty('transportRegistry', $auth);
        /** @var TransportRegistry $registry2 */
        $registry2 = $this->getPrivateProperty('transportRegistry', $auth2);

        self::assertCount(1, $registry->getTransports());
        self::assertInstanceOf(CookieTransport::class, $registry->getTransport('cookie'));
        self::assertCount(1, $registry2->getTransports());
        self::assertInstanceOf(HeaderTransport::class, $registry2->getTransport('header'));
    }

    public function testCloseContextWithAuthContextTransportNull(): void
    {
        $middleware = new Autowire(AuthTransportMiddleware::class, ['header']);

        $auth = $this->getPrivateProperty('authMiddleware', $middleware->resolve($this->container));

        $authContext = $this->createMock(AuthContextInterface::class);
        $authContext->expects($this->once())->method('getTransport')->willReturn(null);
        $authContext->expects($this->once())->method('isClosed')->willReturn(false);
        $authContext->expects($this->exactly(3))->method('getToken')->willReturn(new Token('1', []));

        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())->method('withAddedHeader')->willReturn($response);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())->method('hasHeader')->willReturn(false);

        (new \ReflectionMethod($auth, 'closeContext'))->invoke($auth, $request, $response, $authContext);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $transports = new TransportRegistry();
        $transports->setTransport('cookie', new CookieTransport('/'));
        $transports->setTransport('header', new HeaderTransport());

        $this->container->bind(TransportRegistry::class, $transports);
        $this->container->bind(ScopeInterface::class, $this->container);
        $this->container->bind(ActorProviderInterface::class, new TestAuthHttpProvider());
        $this->container->bind(TokenStorageInterface::class, new TestAuthHttpStorage());
    }

    private function getPrivateProperty(string $property, object $object): mixed
    {
        $ref = new \ReflectionObject($object);

        return $ref->getProperty($property)->getValue($object);
    }
}
