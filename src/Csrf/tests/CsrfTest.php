<?php

declare(strict_types=1);

namespace Spiral\Tests\Csrf;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Spiral\Core\Container;
use Spiral\Csrf\Config\CsrfConfig;
use Spiral\Csrf\Middleware\CsrfFirewall;
use Spiral\Csrf\Middleware\CsrfMiddleware;
use Spiral\Csrf\Middleware\StrictCsrfFirewall;
use Spiral\Http\Config\HttpConfig;
use Spiral\Http\Http;
use Spiral\Http\Pipeline;
use Nyholm\Psr7\ServerRequest;
use Spiral\Telemetry\NullTracer;
use Spiral\Telemetry\TracerInterface;

class CsrfTest extends TestCase
{
    private Container $container;

    public function setUp(): void
    {
        $this->container = new Container();
        $this->container->bind(
            CsrfConfig::class,
            new CsrfConfig(
                [
                    'cookie'   => 'csrf-token',
                    'length'   => 16,
                    'lifetime' => 86400
                ]
            )
        );

        $this->container->bind(
            TracerInterface::class,
            new NullTracer($this->container)
        );

        $this->container->bind(
            ResponseFactoryInterface::class,
            new TestResponseFactory(new HttpConfig(['headers' => []]))
        );
    }

    public function testGet(): void
    {
        $core = $this->httpCore([CsrfMiddleware::class]);
        $core->setHandler(
            static function ($r) {
                return $r->getAttribute(CsrfMiddleware::ATTRIBUTE);
            }
        );

        $response = $this->get($core, '/');
        self::assertSame(200, $response->getStatusCode());

        $cookies = $this->fetchCookies($response);

        self::assertArrayHasKey('csrf-token', $cookies);
        self::assertSame($cookies['csrf-token'], (string)$response->getBody());
    }

    public function testLengthException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->container->bind(
            CsrfConfig::class,
            new CsrfConfig(
                [
                    'cookie'   => 'csrf-token',
                    'length'   => 0,
                    'lifetime' => 86400
                ]
            )
        );

        $core = $this->httpCore([CsrfMiddleware::class]);
        $core->setHandler(
            static function () {
                return 'all good';
            }
        );

        $response = $this->get($core, '/');
    }

    public function testPostForbidden(): void
    {
        $core = $this->httpCore([CsrfMiddleware::class, CsrfFirewall::class]);
        $core->setHandler(
            static function () {
                return 'all good';
            }
        );

        $response = $this->post($core, '/');
        self::assertSame(412, $response->getStatusCode());
    }

    public function testLogicException(): void
    {
        $this->expectException(\LogicException::class);
        $core = $this->httpCore([CsrfFirewall::class]);
        $core->setHandler(
            static function () {
                return 'all good';
            }
        );

        $response = $this->post($core, '/');
    }

    public function testPostOK(): void
    {
        $core = $this->httpCore([CsrfMiddleware::class, CsrfFirewall::class]);
        $core->setHandler(
            static function () {
                return 'all good';
            }
        );

        $response = $this->get($core, '/');
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('all good', (string)$response->getBody());

        $cookies = $this->fetchCookies($response);

        $response = $this->post($core, '/', [], [], ['csrf-token' => $cookies['csrf-token']]);

        self::assertSame(412, $response->getStatusCode());

        $response = $this->post(
            $core,
            '/',
            [
                'csrf-token' => $cookies['csrf-token']
            ],
            [],
            ['csrf-token' => $cookies['csrf-token']]
        );

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('all good', (string)$response->getBody());
    }

    public function testHeaderOK(): void
    {
        $core = $this->httpCore([CsrfMiddleware::class, CsrfFirewall::class]);
        $core->setHandler(
            static function () {
                return 'all good';
            }
        );

        $response = $this->get($core, '/');
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('all good', (string)$response->getBody());

        $cookies = $this->fetchCookies($response);

        $response = $this->post($core, '/', [], [], ['csrf-token' => $cookies['csrf-token']]);

        self::assertSame(412, $response->getStatusCode());

        $response = $this->post(
            $core,
            '/',
            [],
            [
                'X-CSRF-Token' => $cookies['csrf-token']
            ],
            ['csrf-token' => $cookies['csrf-token']]
        );

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('all good', (string)$response->getBody());
    }

    public function testHeaderOKStrict(): void
    {
        $core = $this->httpCore([CsrfMiddleware::class, StrictCsrfFirewall::class]);
        $core->setHandler(
            static function () {
                return 'all good';
            }
        );

        $response = $this->get($core, '/');
        self::assertSame(412, $response->getStatusCode());

        $cookies = $this->fetchCookies($response);

        $response = $this->get($core, '/', [], [], ['csrf-token' => $cookies['csrf-token']]);

        self::assertSame(412, $response->getStatusCode());

        $response = $this->get(
            $core,
            '/',
            [],
            [
                'X-CSRF-Token' => $cookies['csrf-token']
            ],
            ['csrf-token' => $cookies['csrf-token']]
        );

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('all good', (string)$response->getBody());
    }

    protected function httpCore(array $middleware = []): Http
    {
        $config = new HttpConfig(
            [
                'basePath'   => '/',
                'headers'    => [
                    'Content-Type' => 'text/html; charset=UTF-8'
                ],
                'middleware' => $middleware
            ]
        );

        return new Http(
            $config,
            new Pipeline($this->container),
            new TestResponseFactory($config),
            $this->container
        );
    }

    protected function get(
        Http $core,
        $uri,
        array $query = [],
        array $headers = [],
        array $cookies = []
    ): ResponseInterface {
        return $core->handle($this->request($uri, 'GET', $query, $headers, $cookies));
    }

    protected function post(
        Http $core,
        $uri,
        array $data = [],
        array $headers = [],
        array $cookies = []
    ): ResponseInterface {
        return $core->handle($this->request($uri, 'POST', [], $headers, $cookies)->withParsedBody($data));
    }

    protected function request(
        $uri,
        string $method,
        array $query = [],
        array $headers = [],
        array $cookies = []
    ): ServerRequest {
        $request = new ServerRequest($method, $uri, $headers, 'php://input');

        return $request
            ->withQueryParams($query)
            ->withCookieParams($cookies);
    }

    protected function fetchCookies(ResponseInterface $response): array
    {
        $result = [];

        foreach ($response->getHeaders() as $header) {
            foreach ($header as $headerLine) {
                $chunk = explode(';', $headerLine);
                if (!count($chunk) || mb_strpos($chunk[0], '=') === false) {
                    continue;
                }

                $cookie = explode('=', $chunk[0]);
                $result[$cookie[0]] = rawurldecode($cookie[1]);
            }
        }

        return $result;
    }
}
