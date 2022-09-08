<?php

declare(strict_types=1);

namespace Spiral\Tests\Auth;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Auth\AuthContext;
use Spiral\Auth\Middleware\AuthMiddleware;
use Spiral\Auth\TransportRegistry;
use Spiral\Core\Container;
use Spiral\Http\Config\HttpConfig;
use Spiral\Http\Http;
use Spiral\Http\Pipeline;
use Spiral\Tests\Auth\Diactoros\ResponseFactory;
use Nyholm\Psr7\ServerRequest;
use Spiral\Tests\Auth\Stub\TestAuthHttpProvider;
use Spiral\Tests\Auth\Stub\TestAuthHttpStorage;

class AuthMiddlewareTest extends TestCase
{
    private $container;

    public function setUp(): void
    {
        $this->container = new Container();
    }

    public function testAttributeRead(): void
    {
        $http = $this->getCore([]);
        $http->getPipeline()->pushMiddleware(
            new AuthMiddleware(
                $this->container,
                new TestAuthHttpProvider(),
                new TestAuthHttpStorage(),
                new TransportRegistry()
            )
        );

        $http->setHandler(
            static function (ServerRequestInterface $request, ResponseInterface $response): void {
                $response->getBody()->write(
                    get_class($request->getAttribute('authContext'))
                );
            }
        );

        $response = $http->handle(new ServerRequest('GET', ''));

        self::assertSame(['text/html; charset=UTF-8'], $response->getHeader('Content-Type'));
        self::assertSame(AuthContext::class, (string)$response->getBody());
    }

    public function testNoToken(): void
    {
        $http = $this->getCore([]);
        $http->getPipeline()->pushMiddleware(
            new AuthMiddleware(
                $this->container,
                new TestAuthHttpProvider(),
                new TestAuthHttpStorage(),
                new TransportRegistry()
            )
        );

        $http->setHandler(
            static function (ServerRequestInterface $request, ResponseInterface $response): void {
                if ($request->getAttribute('authContext')->getToken() === null) {
                    echo 'no token';
                }
            }
        );

        $response = $http->handle(new ServerRequest('GET', ''));

        self::assertSame(['text/html; charset=UTF-8'], $response->getHeader('Content-Type'));
        self::assertSame('no token', (string)$response->getBody());
    }

    protected function getCore(array $middleware = []): Http
    {
        $config = new HttpConfig([
            'basePath'   => '/',
            'headers'    => [
                'Content-Type' => 'text/html; charset=UTF-8'
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
