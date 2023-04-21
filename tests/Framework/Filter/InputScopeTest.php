<?php

declare(strict_types=1);

namespace Framework\Filter;

use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Spiral\Core\Container;
use Spiral\Filter\InputScope;
use Spiral\Http\Request\InputManager;

final class InputScopeTest extends TestCase
{
    private InputScope $input;
    private ServerRequestInterface $request;

    protected function setUp(): void
    {
        parent::setUp();

        $container = new Container();
        $request = new ServerRequest(
            method: 'POST',
            uri: 'https://site.com/users',
            headers: [
                'Authorization' => 'Bearer 123',
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ],
            body: 'name=John+Doe',
            version: '1.1',
            serverParams: [
                'REMOTE_ADDR' => '123.123.123',
            ]
        );

        $container->bind(
            ServerRequestInterface::class,
            $this->request = $request
                ->withQueryParams(['foo' => 'bar'])
                ->withCookieParams(['baz' => 'qux'])
                ->withParsedBody(['quux' => 'corge'])
                ->withAttribute('foz', 'baf'),
        );

        $this->input = new InputScope(new InputManager($container));
    }

    public function testGetsMethod(): void
    {
        $this->assertSame('POST', $this->input->getValue('method'));
    }

    public function testGetsPath(): void
    {
        $this->assertSame('/users', $this->input->getValue('path'));
    }

    public function testGetsUri(): void
    {
        $uri = $this->input->getValue('uri');
        $this->assertInstanceOf(UriInterface::class, $uri);

        $this->assertSame('https://site.com/users', (string)$uri);
    }

    public function testGetsRequest(): void
    {
        $this->assertSame($this->request, $this->input->getValue('request'));
    }

    public function testGetsBearerToken(): void
    {
        $this->assertSame('123', $this->input->getValue('bearerToken'));
    }

    public function testIsSecure(): void
    {
        $this->assertTrue($this->input->getValue('isSecure'));
    }

    public function testIsAjax(): void
    {
        $this->assertTrue($this->input->getValue('isAjax'));
    }

    public function testIsXmlHttpRequest(): void
    {
        $this->assertTrue($this->input->getValue('isXmlHttpRequest'));
    }

    public function testIsJsonExpected(): void
    {
        $this->assertTrue($this->input->getValue('isJsonExpected', true));
    }

    public function testGetsRemoteAddress(): void
    {
        $this->assertSame('123.123.123', $this->input->getValue('remoteAddress'));
    }

    /**
     * @dataProvider InputBagsDataProvider
     */
    public function testGetsInputBag(string $source, string $name, mixed $expected): void
    {
        $this->assertSame($expected, $this->input->getValue($source, $name));
    }

    public static function InputBagsDataProvider(): \Traversable
    {
        yield 'headers' => ['headers', 'Authorization', 'Bearer 123'];
        yield 'data' => ['data', 'quux', 'corge'];
        yield 'query' => ['query', 'foo', 'bar'];
        yield 'cookies' => ['cookies', 'baz', 'qux'];
        yield 'server' => ['server', 'REMOTE_ADDR', '123.123.123'];
        yield 'attributes' => ['attributes', 'foz', 'baf'];
    }
}
