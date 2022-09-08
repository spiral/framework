<?php

declare(strict_types=1);

namespace Spiral\Tests\Router;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

class IntegrationTest extends TestCase
{
    private $app;

    public function setUp(): void
    {
        parent::setUp();
        $this->app = $this->makeApp(['DEBUG' => true]);
    }

    public function testRoute(): void
    {
        $r = $this->get('/');
        $this->assertStringContainsString('index', $r->getBody()->__toString());
    }

    public function testAttributeRoute(): void
    {
        $r = $this->get('/attribute');
        $this->assertStringContainsString('attribute', $r->getBody()->__toString());
    }

    public function testRoute2(): void
    {
        $r = $this->post('/');
        $this->assertStringContainsString('method', $r->getBody()->__toString());
    }

    public function testRoute3(): void
    {
        $r = $this->get('/page/test');

        $this->assertSame('page-test', $r->getBody()->__toString());
    }

    public function testRoute4(): void
    {
        $r = $this->get('/page/about');

        $this->assertSame('about', $r->getBody()->__toString());
    }

    public function testRoutesWithoutNames(): void
    {
        $r = $this->get('/nameless');
        $this->assertSame('index', $r->getBody()->__toString());

        $r = $this->post('/nameless');
        $this->assertSame('method', $r->getBody()->__toString());

        $r = $this->get('/nameless/route');
        $this->assertSame('route', $r->getBody()->__toString());
    }

    public function get(
        $uri,
        array $query = [],
        array $headers = [],
        array $cookies = []
    ): ResponseInterface {
        return $this->app->getHttp()->handle($this->request($uri, 'GET', $query, $headers, $cookies));
    }

    public function getWithAttributes(
        $uri,
        array $attributes,
        array $headers = []
    ): ResponseInterface {
        $r = $this->request($uri, 'GET', [], $headers, []);
        foreach ($attributes as $k => $v) {
            $r = $r->withAttribute($k, $v);
        }

        return $this->app->getHttp()->handle($r);
    }

    public function post(
        $uri,
        array $data = [],
        array $headers = [],
        array $cookies = []
    ): ResponseInterface {
        return $this->app->getHttp()->handle(
            $this->request($uri, 'POST', [], $headers, $cookies)->withParsedBody($data)
        );
    }

    public function request(
        $uri,
        string $method,
        array $query = [],
        array $headers = [],
        array $cookies = []
    ): ServerRequestInterface {
        $headers = array_merge([
            'accept-language' => 'en'
        ], $headers);

        /** @var ServerRequestFactoryInterface $factory */
        $factory = $this->app->getContainer()->get(ServerRequestFactoryInterface::class);
        $request = $factory->createServerRequest($method, $uri);

        foreach ($headers as $name => $value) {
            $request = $request->withAddedHeader($name, $value);
        }

        return $request
            ->withCookieParams($cookies)
            ->withQueryParams($query);
    }

    public function fetchCookies(array $header)
    {
        $result = [];
        foreach ($header as $line) {
            $cookie = explode('=', $line);
            $result[$cookie[0]] = rawurldecode(substr($cookie[1], 0, strpos($cookie[1], ';')));
        }

        return $result;
    }
}
