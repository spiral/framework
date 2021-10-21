<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Http\ErrorHandler;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Http\ErrorHandler\PlainRenderer;

/**
 * @coversDefaultClass \Spiral\Http\ErrorHandler\PlainRenderer
 */
class PlainRendererTest extends TestCase
{
    public function testContentTypeApplicationJson(): void
    {
        $responseFactory = $this->createMock(ResponseFactoryInterface::class);
        $responseFactory
            ->expects(self::once())
            ->method('createResponse')
            ->willReturnCallback(static function () {
                return new Response();
            });

        $renderer = new PlainRenderer($responseFactory);
        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->expects(self::once())
            ->method('getHeaderLine')
            ->willReturnMap([
                ['Accept', 'application/json'],
            ]);

        $response = $renderer->renderException($request, 400, 'message');
        self::assertTrue($response->hasHeader('Content-Type'));
        self::assertSame(['application/json; charset=UTF-8'], $response->getHeader('Content-Type'));

        $stream = $response->getBody();
        $stream->rewind();
        self::assertJsonStringEqualsJsonString('{"status": 400, "error": "message"}', $stream->getContents());
    }
}
