<?php

declare(strict_types=1);

namespace Spiral\Tests\AuthHttp\Middleware;

use Spiral\Auth\Middleware\AuthMiddleware;
use Spiral\Auth\TokenStorageInterface;
use Spiral\Auth\TokenStorageScope;
use Spiral\Core\Container\Autowire;
use Spiral\Framework\Spiral;
use Spiral\Testing\Attribute\TestScope;
use Spiral\Tests\Framework\HttpTestCase;

final class AuthMiddlewareTest extends HttpTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->enableMiddlewares();
    }

    #[TestScope(Spiral::Http)]
    public function testTokenStorageInterfaceShouldBeBound(): void
    {
        $storage = $this->createMock(TokenStorageInterface::class);
        $this->getContainer()->bind(
            AuthMiddleware::class,
            new Autowire(AuthMiddleware::class, ['tokenStorage' => $storage])
        );

        $this->setHttpHandler(function () use ($storage): void {
            $scope = $this->getContainer()->get(TokenStorageScope::class);
            $ref = new \ReflectionMethod($scope, 'getTokenStorage');

            $this->assertInstanceOf($storage::class, $ref->invoke($scope));
            $this->assertSame($storage, $ref->invoke($scope));
        });

        $this->fakeHttp()->get('/');
    }
}
