<?php

declare(strict_types=1);

namespace Spiral\Tests\Http\Middleware;

use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Spiral\Bootloader\Http\JsonPayloadConfig;
use Spiral\Http\Exception\ClientException;
use Spiral\Http\Middleware\JsonPayloadMiddleware;

final class JsonPayloadMiddlewareTest extends TestCase
{
    public static function parsedBodyDataProvider(): \Traversable
    {
        yield 'object' => ['application/json', '{"foo":"bar"}', ['foo' => 'bar']];
        yield 'list' => ['application/json', '[1,2]', [1, 2]];
        yield 'content type with charset' => ['application/json; charset=utf-8', '{"foo":1}', ['foo' => 1]];
        yield 'null' => ['application/json', 'null', null];
        yield 'empty body' => ['application/json', '', null];
        yield 'not a json content type' => ['text/plain', '{"foo":"bar"}', null];
    }

    public static function invalidPayloadDataProvider(): \Traversable
    {
        yield 'string' => ['"foo"'];
        yield 'integer' => ['123'];
        yield 'float' => ['1.5'];
        yield 'boolean' => ['true'];
        yield 'malformed' => ['{"foo":'];
    }

    #[DataProvider('parsedBodyDataProvider')]
    public function testParsedBody(string $contentType, string $body, ?array $expected): void
    {
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler
            ->expects(self::once())
            ->method('handle')
            ->with(self::callback(static fn(ServerRequestInterface $r): bool => $r->getParsedBody() === $expected))
            ->willReturn(new Response());

        $this->createMiddleware()->process($this->createRequest($contentType, $body), $handler);
    }

    #[DataProvider('invalidPayloadDataProvider')]
    public function testInvalidPayload(string $body): void
    {
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects(self::never())->method('handle');

        $this->expectException(ClientException::class);
        $this->expectExceptionCode(400);
        $this->expectExceptionMessage('invalid json payload');

        $this->createMiddleware()->process($this->createRequest('application/json', $body), $handler);
    }

    private function createMiddleware(): JsonPayloadMiddleware
    {
        return new JsonPayloadMiddleware(new JsonPayloadConfig());
    }

    private function createRequest(string $contentType, string $body): ServerRequest
    {
        return new ServerRequest('POST', '/', ['Content-Type' => $contentType], $body);
    }
}
