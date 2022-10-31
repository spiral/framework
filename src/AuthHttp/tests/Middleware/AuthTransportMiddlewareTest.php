<?php

declare(strict_types=1);

namespace Spiral\Tests\Auth\Middleware;

use Spiral\Auth\ActorProviderInterface;
use Spiral\Auth\Middleware\AuthTransportMiddleware;
use Spiral\Auth\TokenStorageInterface;
use Spiral\Auth\Transport\CookieTransport;
use Spiral\Auth\Transport\HeaderTransport;
use Spiral\Auth\TransportRegistry;
use Spiral\Core\Container\Autowire;
use Spiral\Core\ScopeInterface;
use Spiral\Tests\Auth\BaseTest;
use Spiral\Tests\Auth\Stub\TestAuthHttpProvider;
use Spiral\Tests\Auth\Stub\TestAuthHttpStorage;

final class AuthTransportMiddlewareTest extends BaseTest
{
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

        $this->assertCount(1, $registry->getTransports());
        $this->assertInstanceOf(CookieTransport::class, $registry->getTransport('cookie'));
        $this->assertCount(1, $registry2->getTransports());
        $this->assertInstanceOf(HeaderTransport::class, $registry2->getTransport('header'));
    }

    private function getPrivateProperty(string $property, object $object): mixed
    {
        $ref = new \ReflectionObject($object);

        return $ref->getProperty($property)->getValue($object);
    }
}
