<?php

declare(strict_types=1);

namespace Spiral\Tests\Auth;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Auth\HttpTransportInterface;
use Spiral\Auth\Middleware\AuthMiddleware;
use Spiral\Auth\Transport\CookieTransport;
use Spiral\Auth\TransportRegistry;
use Spiral\Cookies\Cookie\SameSite;
use Spiral\Http\Config\HttpConfig;
use Spiral\Http\Http;
use Spiral\Http\Pipeline;
use Spiral\Tests\Auth\Diactoros\ResponseFactory;
use Nyholm\Psr7\ServerRequest;
use Spiral\Tests\Auth\Stub\TestAuthHttpProvider;
use Spiral\Tests\Auth\Stub\TestAuthHttpStorage;
use Spiral\Tests\Auth\Stub\TestAuthHttpToken;

final class CookieTransportTest extends BaseTestCase
{
    public function testCookieToken(): void
    {
        $http = $this->getCore(new CookieTransport('auth-token'));

        $http->setHandler(
            static function (ServerRequestInterface $request, ResponseInterface $response): void {
                if ($request->getAttribute('authContext')->getToken() === null) {
                    echo 'no token';
                } else {
                    echo $request->getAttribute('authContext')->getToken()->getID();
                    echo ':';
                    echo \json_encode($request->getAttribute('authContext')->getToken()->getPayload());
                }
            },
        );

        $response = $http->handle(
            (new ServerRequest('GET', '', body: 'php://input'))->withCookieParams(['auth-token' => 'good-token']),
        );

        self::assertSame(['text/html; charset=UTF-8'], $response->getHeader('Content-Type'));
        self::assertSame('good-token:{"id":"good-token"}', (string) $response->getBody());
    }

    public function testBadCookieToken(): void
    {
        $http = $this->getCore(new CookieTransport('auth-token'));

        $http->setHandler(
            static function (ServerRequestInterface $request, ResponseInterface $response): void {
                if ($request->getAttribute('authContext')->getToken() === null) {
                    echo 'no token';
                } else {
                    echo $request->getAttribute('authContext')->getToken()->getID();
                    echo ':';
                    echo \json_encode($request->getAttribute('authContext')->getToken()->getPayload());
                }
            },
        );

        $response = $http->handle(
            (new ServerRequest('GET', '', body: 'php://input'))->withCookieParams(['auth-token' => 'bad']),
        );

        self::assertSame(['text/html; charset=UTF-8'], $response->getHeader('Content-Type'));
        self::assertSame('no token', (string) $response->getBody());
    }

    public function testDeleteToken(): void
    {
        $http = $this->getCore(new CookieTransport('auth-token'));

        $http->setHandler(
            static function (ServerRequestInterface $request, ResponseInterface $response): void {
                $request->getAttribute('authContext')->close();

                echo 'closed';
            },
        );

        $response = $http->handle(
            (new ServerRequest('GET', '', body: 'php://input'))->withCookieParams(['auth-token' => 'good-token']),
        );

        self::assertSame(['auth-token=; Path=/; HttpOnly'], $response->getHeader('Set-Cookie'));
        self::assertSame('closed', (string) $response->getBody());
    }

    public function testCommitToken(): void
    {
        $http = $this->getCore(new CookieTransport('auth-token'));

        $http->setHandler(
            static function (ServerRequestInterface $request, ResponseInterface $response): void {
                $request->getAttribute('authContext')->start(
                    new TestAuthHttpToken('new-token', ['ok' => 1]),
                );
            },
        );

        $response = $http->handle(new ServerRequest('GET', '', body: 'php://input'));

        self::assertSame(['auth-token=new-token; Path=/; HttpOnly'], $response->getHeader('Set-Cookie'));
    }

    public function testCommitTokenOtherParams(): void
    {
        $http = $this->getCore(
            new CookieTransport('auth-token', '/', 'localhost', true, false, SameSite::NONE),
        );

        $http->setHandler(
            static function (ServerRequestInterface $request, ResponseInterface $response): void {
                $request->getAttribute('authContext')->start(
                    new TestAuthHttpToken('new-token', ['ok' => 1]),
                );
            },
        );

        $response = $http->handle(new ServerRequest('GET', '', body: 'php://input'));

        self::assertSame(
            ['auth-token=new-token; Path=/; Domain=localhost; Secure; SameSite=None'],
            $response->getHeader('Set-Cookie'),
        );
    }

    public function testCommitTokenLifetime(): void
    {
        $http = $this->getCore(new CookieTransport('auth-token'));

        $http->setHandler(
            static function (ServerRequestInterface $request, ResponseInterface $response): void {
                $request->getAttribute('authContext')->start(
                    new TestAuthHttpToken('new-token', ['ok' => 1], (new \DateTime('now'))->modify('+1 hour')),
                );
            },
        );

        $response = $http->handle(new ServerRequest('GET', '', body: 'php://input'));

        $cookie = \explode('; ', $response->getHeader('Set-Cookie')[0]);

        self::assertSame(
            'auth-token=new-token',
            $cookie[0],
        );

        self::assertSame(
            'Expires=' . \gmdate(DATE_COOKIE, \time() + 3600),
            $cookie[1],
        );

        self::assertSame(
            'Max-Age=3600',
            $cookie[2],
        );
    }

    protected function getCore(HttpTransportInterface $transport): Http
    {
        $config = new HttpConfig(
            [
                'basePath'   => '/',
                'headers'    => [
                    'Content-Type' => 'text/html; charset=UTF-8',
                ],
                'middleware' => [],
            ],
        );

        $http = new Http(
            $config,
            new Pipeline($this->container),
            new ResponseFactory($config),
            $this->container,
        );

        $http->getPipeline()->pushMiddleware(
            new AuthMiddleware(
                $this->container,
                new TestAuthHttpProvider(),
                new TestAuthHttpStorage(),
                $reg = new TransportRegistry(),
            ),
        );
        $reg->setDefaultTransport('transport');
        $reg->setTransport('transport', $transport);

        return $http;
    }
}
