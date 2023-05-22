<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Http\ErrorHandler;

use GuzzleHttp\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Spiral\Http\ErrorHandler\PlainRenderer;

/**
 * @coversDefaultClass \Spiral\Http\ErrorHandler\PlainRenderer
 */
final class PlainRendererTest extends TestCase
{
    public function testContentTypeApplicationJson(): void
    {
        $renderer = new PlainRenderer($this->mockResponseFactory());
        $request = new ServerRequest('GET', '', body: 'php://input', headers: [
            'Accept' => 'application/json',
        ]);

        $response = $renderer->renderException($request, 400, new \Exception('message'));
        self::assertTrue($response->hasHeader('Content-Type'));
        self::assertSame(['application/json; charset=UTF-8'], $response->getHeader('Content-Type'));

        $stream = $response->getBody();
        $stream->rewind();
        self::assertJsonStringEqualsJsonString('{"status": 400}', $stream->getContents());
    }

    public function testNoAcceptHeader(): void
    {
        $renderer = new PlainRenderer($this->mockResponseFactory());
        $request = new ServerRequest('GET', '', body: 'php://input');

        $response = $renderer->renderException($request, 400, new \Exception('message'));
        $stream = $response->getBody();
        $stream->rewind();
        self::assertEquals('Error code: 400', $stream->getContents());
    }

    #[DataProvider('dataResponseIsJson')]
    public function testResponseIsJson(): void
    {
        $renderer = new PlainRenderer($this->mockResponseFactory());
        $request = new ServerRequest('GET', '', [
            'Accept' => [
                'application/json',
                'application/json',
            ],
        ], 'php://input');

        $response = $renderer->renderException($request, 400, new \Exception('message'));
        self::assertTrue($response->hasHeader('Content-Type'));
        self::assertSame(['application/json; charset=UTF-8'], $response->getHeader('Content-Type'));

        $stream = $response->getBody();
        $stream->rewind();
        self::assertJsonStringEqualsJsonString('{"status": 400}', $stream->getContents());
    }

    public static function dataResponseIsJson(): iterable
    {
        yield [
            'application/json',
        ];

        //Client and Server set `Accept` header each
        yield [
            ['application/json', 'application/json'],
        ];

        yield [
            'application/json, text/html;q=0.9, */*;q=0.8',
        ];

        yield [
            [
                'application/json',
                'text/html, application/json;q=0.9, */*;q=0.8',
            ],
        ];
    }

    #[DataProvider('dataResponseIsPlain')]
    public function testResponseIsPlain($acceptHeader): void
    {
        $renderer = new PlainRenderer($this->mockResponseFactory());
        $request = new ServerRequest('GET', '', ['Accept' => $acceptHeader], 'php://input');

        $response = $renderer->renderException($request, 400, new \Exception('message'));
        $stream = $response->getBody();
        $stream->rewind();
        self::assertEquals('Error code: 400', $stream->getContents());
    }

    public static function dataResponseIsPlain(): iterable
    {
        //Accept header contains several mime types with `q` values. JSON is not prioritized
        yield [
            'text/html, application/json;q=0.9, */*;q=0.8',
        ];

        yield [
            [
                'text/html, application/json;q=0.9, */*;q=0.8',
                'application/json',
            ],
        ];

        yield [
            'text/html, application/json, */*;q=0.9',
        ];
    }

    private function mockResponseFactory(): ResponseFactoryInterface
    {
        $responseFactory = $this->createMock(ResponseFactoryInterface::class);
        $responseFactory
            ->expects(self::once())
            ->method('createResponse')
            ->willReturnCallback(static function () {
                return new Response();
            });
        return $responseFactory;
    }
}
