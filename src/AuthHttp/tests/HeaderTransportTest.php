<?php

declare(strict_types=1);

namespace Spiral\Tests\Auth;

use Psr\Http\Message\ServerRequestInterface;
use Spiral\Auth\HttpTransportInterface;
use Spiral\Auth\Middleware\AuthMiddleware;
use Spiral\Auth\Transport\HeaderTransport;
use Spiral\Auth\TransportRegistry;
use Spiral\Http\Config\HttpConfig;
use Spiral\Http\Http;
use Spiral\Http\Pipeline;
use Spiral\Tests\Auth\Diactoros\ResponseFactory;
use Nyholm\Psr7\ServerRequest;
use Spiral\Tests\Auth\Stub\TestAuthHttpProvider;
use Spiral\Tests\Auth\Stub\TestAuthHttpStorage;
use Spiral\Tests\Auth\Stub\TestAuthHttpToken;

final class HeaderTransportTest extends BaseTestCase
{
    public function testHeaderToken(): void
    {
        $http = $this->getCore(new HeaderTransport());

        $http->setHandler(
            static function (ServerRequestInterface $request): void {
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
            new ServerRequest('GET', '', ['X-Auth-Token' => 'good-token'], 'php://input'),
        );

        self::assertSame(['text/html; charset=UTF-8'], $response->getHeader('Content-Type'));
        self::assertSame('good-token:{"id":"good-token"}', (string) $response->getBody());
    }

    public function testHeaderTokenWithCustomValueFormat(): void
    {
        $http = $this->getCore(new HeaderTransport('Authorization', 'Bearer %s'));

        $http->setHandler(
            static function (ServerRequestInterface $request): void {
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
            new ServerRequest('GET', '', ['Authorization' => 'Bearer good-token'], 'php://input'),
        );

        self::assertSame(['text/html; charset=UTF-8'], $response->getHeader('Content-Type'));
        self::assertSame('good-token:{"id":"good-token"}', (string) $response->getBody());
    }

    public function testBadHeaderToken(): void
    {
        $http = $this->getCore(new HeaderTransport());

        $http->setHandler(
            static function (ServerRequestInterface $request): void {
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
            new ServerRequest('GET', '', ['X-Auth-Token' => 'bad'], 'php://input'),
        );

        self::assertSame(['text/html; charset=UTF-8'], $response->getHeader('Content-Type'));
        self::assertSame('no token', (string) $response->getBody());
    }

    public function testDeleteToken(): void
    {
        $http = $this->getCore(new HeaderTransport());

        $http->setHandler(
            static function (ServerRequestInterface $request): void {
                $request->getAttribute('authContext')->close();
                echo 'closed';
            },
        );
        $response = $http->handle(
            new ServerRequest('GET', '', ['X-Auth-Token' => 'bad'], 'php://input'),
        );

        self::assertEmpty($response->getHeader('X-Auth-Token'));
        self::assertSame('closed', (string) $response->getBody());
    }

    public function testCommitToken(): void
    {
        $http = $this->getCore(new HeaderTransport());

        $http->setHandler(
            static function (ServerRequestInterface $request): void {
                $request->getAttribute('authContext')->start(
                    new TestAuthHttpToken('new-token', ['ok' => 1]),
                );
            },
        );

        $response = $http->handle(new ServerRequest('GET', '', body: 'php://input'));

        self::assertSame(['new-token'], $response->getHeader('X-Auth-Token'));
    }

    public function testCommitTokenWithCustomValueFormat(): void
    {
        $http = $this->getCore(new HeaderTransport('Authorization', 'Bearer %s'));

        $http->setHandler(
            static function (ServerRequestInterface $request): void {
                $request->getAttribute('authContext')->start(
                    new TestAuthHttpToken('new-token', ['ok' => 1]),
                );
            },
        );

        $response = $http->handle(new ServerRequest('GET', '', body: 'php://input'));

        self::assertSame(['Bearer new-token'], $response->getHeader('Authorization'));
    }

    protected function getCore(HttpTransportInterface $transport): Http
    {
        $config = new HttpConfig([
            'basePath'   => '/',
            'headers'    => [
                'Content-Type' => 'text/html; charset=UTF-8',
            ],
            'middleware' => [],
        ]);

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
