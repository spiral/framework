<?php

declare(strict_types=1);

namespace Spiral\Tests\Http;

use PHPUnit\Framework\TestCase;
use Spiral\Files\Files;
use Spiral\Http\Config\HttpConfig;
use Spiral\Http\Exception\ResponseException;
use Spiral\Http\ResponseWrapper;
use Spiral\Tests\Http\Diactoros\ResponseFactory;
use Spiral\Tests\Http\Diactoros\StreamFactory;
use Nyholm\Psr7\Stream;

class ResponsesTest extends TestCase
{
    public function testRedirect(): void
    {
        $response = $this->getWrapper()->redirect('google.com');
        self::assertSame('google.com', $response->getHeaderLine('Location'));
        self::assertSame(302, $response->getStatusCode());

        $response = $this->getWrapper()->redirect('google.com', 301);
        self::assertSame('google.com', $response->getHeaderLine('Location'));
        self::assertSame(301, $response->getStatusCode());
    }

    public function testJson(): void
    {
        $response = $this->getWrapper()->json([
            'status'  => 300,
            'message' => 'hi'
        ]);

        self::assertSame('{"status":300,"message":"hi"}', (string)$response->getBody());
        self::assertSame(300, $response->getStatusCode());
        self::assertSame('application/json', $response->getHeaderLine('Content-Type'));
    }

    public function testHtml(): void
    {
        $response = $this->getWrapper()->html('hello world');
        self::assertSame('hello world', (string)$response->getBody());
        self::assertSame(200, $response->getStatusCode());
        $response->getHeader('Content-Type');
        self::assertSame(['text/html; charset=utf-8'], $response->getHeader('Content-Type'));
    }

    public function testAttachment(): void
    {
        $response = $this->getWrapper()->attachment(__FILE__);

        self::assertSame(200, $response->getStatusCode());
        self::assertStringEqualsFile(__FILE__, (string)$response->getBody());
        self::assertSame(filesize(__FILE__), $response->getBody()->getSize());
        self::assertSame('application/octet-stream', $response->getHeaderLine('Content-Type'));
    }

    public function testAttachmentResource(): void
    {
        $response = $this->getWrapper()->attachment(fopen(__FILE__, 'r'), 'file.php');

        self::assertSame(200, $response->getStatusCode());
        self::assertStringEqualsFile(__FILE__, (string)$response->getBody());
        self::assertSame(filesize(__FILE__), $response->getBody()->getSize());
        self::assertSame('application/octet-stream', $response->getHeaderLine('Content-Type'));
    }

    public function testAttachmentStream(): void
    {
        $response = $this->getWrapper()->attachment(Stream::create(fopen(__FILE__, 'r')), 'file.php');

        self::assertSame(200, $response->getStatusCode());
        self::assertStringEqualsFile(__FILE__, (string)$response->getBody());
        self::assertSame(filesize(__FILE__), $response->getBody()->getSize());
        self::assertSame('application/octet-stream', $response->getHeaderLine('Content-Type'));
    }

    public function testAttachmentStreamable(): void
    {
        $response = $this->getWrapper()->attachment(
            new Streamable(Stream::create(fopen(__FILE__, 'r'))),
            'file.php'
        );

        self::assertSame(200, $response->getStatusCode());
        self::assertStringEqualsFile(__FILE__, (string)$response->getBody());
        self::assertSame(filesize(__FILE__), $response->getBody()->getSize());
        self::assertSame('application/octet-stream', $response->getHeaderLine('Content-Type'));
    }

    public function testCreate(): void
    {
        $response = $this->getWrapper()->create(400);

        self::assertSame(400, $response->getStatusCode());
    }

    public function testAttachmentStreamNoName(): void
    {
        $this->expectException(ResponseException::class);
        $this->getWrapper()->attachment(Stream::create(fopen(__FILE__, 'rb')));
    }

    public function testAttachmentException(): void
    {
        $this->expectException(ResponseException::class);
        $this->getWrapper()->attachment('invalid');
    }

    protected function getWrapper(): ResponseWrapper
    {
        return new ResponseWrapper(
            new ResponseFactory(new HttpConfig(['headers' => []])),
            new StreamFactory(),
            new Files()
        );
    }
}
