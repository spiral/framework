<?php

declare(strict_types=1);

namespace Spiral\Tests\Http\Telemetry;

use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Spiral\Http\Telemetry\HttpSpanAttributes;
use Spiral\Telemetry\Span;

final class HttpSpanAttributesTest extends TestCase
{
    public static function knownMethodsProvider(): iterable
    {
        foreach (['CONNECT', 'DELETE', 'GET', 'HEAD', 'OPTIONS', 'PATCH', 'POST', 'PUT', 'QUERY', 'TRACE'] as $method) {
            yield $method => [$method];
        }
    }

    public static function unsetStatusProvider(): iterable
    {
        yield 'ok' => [200];
        yield 'redirect' => [302];
        yield 'not found' => [404];
        yield 'unprocessable' => [422];
    }

    #[DataProvider('knownMethodsProvider')]
    public function testKnownMethodIsKept(string $method): void
    {
        $request = new ServerRequest($method, 'https://example.org/');

        self::assertSame($method, HttpSpanAttributes::serverSpanName($request));
        $attributes = HttpSpanAttributes::serverRequest($request);
        self::assertSame($method, $attributes['http.request.method']);
        self::assertArrayNotHasKey('http.request.method_original', $attributes);
    }

    public function testUnknownMethodBecomesOther(): void
    {
        $request = new ServerRequest('get', 'https://example.org/');

        self::assertSame('HTTP', HttpSpanAttributes::serverSpanName($request));
        $attributes = HttpSpanAttributes::serverRequest($request);
        self::assertSame('_OTHER', $attributes['http.request.method']);
        self::assertSame('get', $attributes['http.request.method_original']);
    }

    public function testServerRequestAttributes(): void
    {
        $request = new ServerRequest(
            'POST',
            'https://example.org:8443/users/42?sort=name',
            [
                'Authorization' => ['Bearer secret'],
                'Cookie' => ['session=secret'],
                'User-Agent' => ['curl/8.0'],
            ],
            version: '2',
            serverParams: ['REMOTE_ADDR' => '192.0.2.10'],
        );

        self::assertSame(
            [
                'http.request.method' => 'POST',
                'url.scheme' => 'https',
                'url.path' => '/users/42',
                'url.query' => 'sort=name',
                'server.address' => 'example.org',
                'server.port' => 8443,
                'client.address' => '192.0.2.10',
                'user_agent.original' => 'curl/8.0',
                'network.protocol.version' => '2',
            ],
            HttpSpanAttributes::serverRequest($request),
        );
    }

    public function testDefaultPortIsDerivedFromScheme(): void
    {
        $attributes = HttpSpanAttributes::serverRequest(new ServerRequest('GET', 'https://example.org'));

        self::assertSame(443, $attributes['server.port']);
        self::assertSame('/', $attributes['url.path']);
        self::assertArrayNotHasKey('url.query', $attributes);
    }

    public function testRequestWithoutHost(): void
    {
        $attributes = HttpSpanAttributes::serverRequest(new ServerRequest('GET', '/path'));

        self::assertArrayNotHasKey('server.address', $attributes);
        self::assertArrayNotHasKey('server.port', $attributes);
        self::assertArrayNotHasKey('client.address', $attributes);
        self::assertArrayNotHasKey('user_agent.original', $attributes);
    }

    #[DataProvider('unsetStatusProvider')]
    public function testStatusIsLeftUnsetBelow500(int $statusCode): void
    {
        $span = new Span('GET');

        HttpSpanAttributes::applyResponse($span, new Response($statusCode, body: 'hello'));

        self::assertNull($span->getStatus());
        self::assertSame(
            ['http.response.status_code' => $statusCode, 'http.response.body.size' => 5],
            $span->getAttributes(),
        );
    }

    public function testServerErrorSetsErrorStatus(): void
    {
        $span = new Span('GET');

        HttpSpanAttributes::applyResponse($span, new Response(503, ['Content-Length' => '12']));

        self::assertSame('ERROR', $span->getStatus()?->code);
        self::assertSame(
            ['http.response.status_code' => 503, 'http.response.body.size' => 12, 'error.type' => '503'],
            $span->getAttributes(),
        );
    }
}
