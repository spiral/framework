<?php

declare(strict_types=1);

namespace Spiral\Tests\Http\Request;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Core\Container;
use Spiral\Core\Exception\ScopeException;
use Spiral\Http\Config\HttpConfig;
use Spiral\Http\Exception\InputException;
use Spiral\Http\Request\FilesBag;
use Spiral\Http\Request\HeadersBag;
use Spiral\Http\Request\InputBag;
use Spiral\Http\Request\InputManager;
use Spiral\Http\Request\ServerBag;
use Nyholm\Psr7\ServerRequest;

class InputManagerTest extends TestCase
{
    private Container $container;
    private InputManager $input;

    public function setUp(): void
    {
        $this->container = new Container();
        $this->input = new InputManager($this->container);
    }

    public function testCreateOutsideOfScope(): void
    {
        $this->expectException(ScopeException::class);
        $this->input->request();
    }

    public function testGetRequest(): void
    {
        $this->container->bind(ServerRequestInterface::class, new ServerRequest('GET', ''));

        $this->assertNotNull($this->input->request());
        $this->assertSame($this->input->request(), $this->input->request());
    }

    public function testChangeRequest(): void
    {
        $this->container->bind(ServerRequestInterface::class, new ServerRequest('GET', '/hello'));
        $this->assertSame('/hello', $this->input->path());

        $this->container->bind(ServerRequestInterface::class, new ServerRequest('GET', '/other'));
        $this->assertSame('/other', $this->input->path());
    }

    public function testUri(): void
    {
        $request = new ServerRequest('GET', 'http://domain.com/hello-world');
        $this->container->bind(ServerRequestInterface::class, $request);

        $this->assertSame('/hello-world', $this->input->path());

        $request = new ServerRequest('GET', 'http://domain.com/new-one');
        $this->container->bind(ServerRequestInterface::class, $request);

        $this->assertSame('/new-one', $this->input->path());

        $request = new ServerRequest('GET', '');
        $this->container->bind(ServerRequestInterface::class, $request);

        $this->assertSame('/', $this->input->path());


        $request = new ServerRequest('GET', 'hello');
        $this->container->bind(ServerRequestInterface::class, $request);

        $this->assertSame('/hello', $this->input->path());
    }

    public function testMethod(): void
    {
        $request = new ServerRequest('GET', 'http://domain.com/hello-world');
        $this->container->bind(ServerRequestInterface::class, $request);

        $this->assertSame('GET', $this->input->method());

        $request = new ServerRequest('POST', 'http://domain.com/hello-world');
        $this->container->bind(ServerRequestInterface::class, $request);

        $this->assertSame('POST', $this->input->method());

        //case fixing
        $request = new ServerRequest('put', 'http://domain.com/hello-world');
        $this->container->bind(ServerRequestInterface::class, $request);

        $this->assertSame('PUT', $this->input->method());
    }

    public function testIsSecure(): void
    {
        $request = new ServerRequest('GET', 'http://domain.com/hello-world');
        $this->container->bind(ServerRequestInterface::class, $request);

        $this->assertFalse($this->input->isSecure());

        $request = new ServerRequest('POST', 'https://domain.com/hello-world');
        $this->container->bind(ServerRequestInterface::class, $request);

        $this->assertTrue($this->input->isSecure());
    }

    public function testBearerToken(): void
    {
        $request = new ServerRequest('GET', 'http://domain.com/hello-world');
        $this->container->bind(ServerRequestInterface::class, $request);

        $this->assertNull($this->input->bearerToken());

        $request = new ServerRequest(method: 'GET', uri: 'http://domain.com/hello-world', headers: [
            'Authorization' => 'Bearer some-token'
        ]);

        $this->container->bind(ServerRequestInterface::class, $request);
        $this->assertSame('some-token', $this->input->bearerToken());

        // Case with coma separated header values
        $request = new ServerRequest(method: 'GET', uri: 'http://domain.com/hello-world', headers: [
            'Authorization' => 'Bearer some-token'
        ]);

        $this->container->bind(ServerRequestInterface::class, $request->withAddedHeader('Authorization', 'baz'));
        $this->assertSame('some-token', $this->input->bearerToken());
    }

    public function testIsAjax(): void
    {
        $request = new ServerRequest('GET', 'http://domain.com/hello-world', body: 'php://input');
        $this->container->bind(ServerRequestInterface::class, $request);

        $this->assertFalse($this->input->isAjax());

        $request = new ServerRequest(
            'GET',
            'http://domain.com/hello-world',
            ['X-Requested-With' => 'xmlhttprequest'],
            'php://input'
        );
        $this->container->bind(ServerRequestInterface::class, $request);

        $this->assertTrue($this->input->isAjax());
    }

    public function testIsXmlHttpRequest(): void
    {
        $request = new ServerRequest('GET', 'http://domain.com/hello-world');
        $this->container->bind(ServerRequestInterface::class, $request);

        $this->assertFalse($this->input->isXmlHttpRequest());

        $request = new ServerRequest(
            'GET',
            'http://domain.com/hello-world',
            ['X-Requested-With' => 'xmlhttprequest'],
        );
        $this->container->bind(ServerRequestInterface::class, $request);

        $this->assertTrue($this->input->isXmlHttpRequest());
    }

    /**
     * @dataProvider isJsonExpectedProvider
     * @param bool        $expected
     * @param string|null $acceptHeader
     */
    public function testIsJsonExpected(bool $expected, ?string $acceptHeader): void
    {
        $input = $this->input->withJsonType('application/vnd.api+json');

        $request = new ServerRequest(
            'GET',
            'http://domain.com/hello-world',
            $acceptHeader !== null ? ['Accept' => $acceptHeader] : [],
            'php://input'
        );
        $this->container->bind(ServerRequestInterface::class, $request);

        $this->assertSame($expected, $input->isJsonExpected());
    }

    /**
     * @return iterable
     */
    public function isJsonExpectedProvider(): iterable
    {
        return [
            [false, null],
            [false, 'text/html'],
            [true, 'application/json'],
            [true, 'application/vnd.api+json'],
        ];
    }

    /**
     * @dataProvider isJsonExpectedOnSoftMatchProvider
     * @param bool        $expected
     * @param string|null $acceptHeader
     */
    public function testIsJsonExpectedOnSoftMatch(bool $expected, ?string $acceptHeader): void
    {
        $request = new ServerRequest(
            'GET',
            'http://domain.com/hello-world',
            $acceptHeader !== null ? ['Accept' => $acceptHeader] : [],
            'php://input'
        );
        $this->container->bind(ServerRequestInterface::class, $request);

        $this->assertFalse($this->input->isJsonExpected());
        $this->assertSame($expected, $this->input->isJsonExpected(true));
    }

    /**
     * @return iterable
     */
    public function isJsonExpectedOnSoftMatchProvider(): iterable
    {
        return [
            [false, null],
            [false, 'text/html'],
            [true, 'text/json'],
            [true, 'application/vnd.api+json'],
        ];
    }

    public function testRemoteIP(): void
    {
        $request = new ServerRequest(
            'GET',
            'http://domain.com/hello-world',
            body: 'php://input',
            serverParams: ['REMOTE_ADDR' => '127.0.0.1']
        );
        $this->container->bind(ServerRequestInterface::class, $request);

        $this->assertSame('127.0.0.1', $this->input->remoteAddress());

        $request = new ServerRequest(
            'GET',
            'http://domain.com/hello-world',
            ['Accept' => 'application/json'],
            'php://input',
            serverParams: ['REMOTE_ADDR' => null]
        );
        $this->container->bind(ServerRequestInterface::class, $request);

        $this->assertTrue($this->input->isJsonExpected());

        $this->assertNull($this->input->remoteAddress());
    }

    public function testGetBag(): void
    {
        $request = new ServerRequest(
            'GET',
            'http://domain.com/hello-world',
            body: 'php://input'
        );
        $this->container->bind(ServerRequestInterface::class, $request);

        $this->assertInstanceOf(ServerBag::class, $this->input->server);
        $this->assertInstanceOf(InputBag::class, $this->input->attributes);
        $this->assertInstanceOf(InputBag::class, $this->input->data);
        $this->assertInstanceOf(InputBag::class, $this->input->cookies);
        $this->assertInstanceOf(InputBag::class, $this->input->query);
        $this->assertInstanceOf(FilesBag::class, $this->input->files);
        $this->assertInstanceOf(HeadersBag::class, $this->input->headers);

        $this->assertInstanceOf(ServerBag::class, $this->input->server);
        $this->assertInstanceOf(InputBag::class, $this->input->attributes);
        $this->assertInstanceOf(InputBag::class, $this->input->data);
        $this->assertInstanceOf(InputBag::class, $this->input->cookies);
        $this->assertInstanceOf(InputBag::class, $this->input->query);
        $this->assertInstanceOf(FilesBag::class, $this->input->files);
        $this->assertInstanceOf(HeadersBag::class, $this->input->headers);

        $input = clone $this->input;
        $this->assertInstanceOf(ServerBag::class, $input->server);
    }

    public function testWrongBad(): void
    {
        $this->expectException(InputException::class);
        $request = new ServerRequest(
            'GET',
            'http://domain.com/hello-world',
            body: 'php://input'
        );

        $this->container->bind(ServerRequestInterface::class, $request);
        $this->input->invalid;
    }

    public function testShortcuts(): void
    {
        $this->container->bind(ServerRequestInterface::class, (new ServerRequest('GET', ''))->withParsedBody([
            'array' => [
                'key' => [
                    'name' => 'value'
                ]
            ],
            'name'  => 'xx'
        ])->withQueryParams([
            'name' => 'value',
            'key'  => ['name' => 'hi']
        ])->withAttribute('attr', 'value')->withCookieParams([
            'cookie' => 'cookie-value'
        ]));

        $this->assertSame('value', $this->input->data('array.key.name'));
        $this->assertSame('value', $this->input->post('array.key.name'));

        $this->assertSame('value', $this->input->query('name'));
        $this->assertSame('hi', $this->input->query('key.name'));

        $this->assertSame('xx', $this->input->input('name'));
        $this->assertSame('hi', $this->input->input('key.name'));
        $this->assertSame('value', $this->input->attribute('attr'));

        $this->assertSame('cookie-value', $this->input->cookie('cookie'));
    }

    public function testAddCustomInputBag(): void
    {
        $input = new InputManager($this->container, new HttpConfig(
            ['inputBags' => ['test' => ['class'  => InputBag::class, 'source' => 'getQueryParams']]]
        ));

        $request = new ServerRequest(
            'GET',
            'http://domain.com/hello-world',
            body: 'php://input'
        );
        $this->container->bind(ServerRequestInterface::class, $request);

        $this->assertInstanceOf(InputBag::class, $input->test);
    }
}
