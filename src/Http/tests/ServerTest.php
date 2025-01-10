<?php

declare(strict_types=1);

namespace Spiral\Tests\Http;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Core\Container;
use Spiral\Http\Exception\InputException;
use Spiral\Http\Request\InputManager;
use Nyholm\Psr7\ServerRequest;

class ServerTest extends TestCase
{
    private Container $container;

    private InputManager $input;

    protected function setUp(): void
    {
        $this->container = new Container();
        $this->input = new InputManager($this->container);
    }

    public function testShortcut(): void
    {
        $request = new ServerRequest(
            'GET',
            '',
            serverParams: ['PATH' => 'sample']
        );

        $this->container->bind(ServerRequestInterface::class, $request);

        self::assertSame('sample', $this->input->server('path'));
    }

    public function testHas(): void
    {
        $request = new ServerRequest(
            'GET',
            '',
            serverParams: ['PATH' => 'sample']
        );

        $this->container->bind(ServerRequestInterface::class, $request);

        self::assertTrue($this->input->server->has('path'));
        self::assertFalse($this->input->server->has('another'));
        self::assertTrue($this->input->server->has('path'));
    }

    public function testGet(): void
    {
        $request = new ServerRequest(
            'GET',
            '',
            serverParams: ['PATH' => 'sample']
        );

        $this->container->bind(ServerRequestInterface::class, $request);

        self::assertSame('sample', $this->input->server->get('path'));
        self::assertNull($this->input->server->get('other'));
    }

    public function testGetDot(): void
    {
        $request = new ServerRequest(
            'GET',
            '',
            serverParams: ['PATH' => ['SAMPLE' => 1]]
        );

        $this->container->bind(ServerRequestInterface::class, $request);

        self::assertSame(1, $this->input->server->get('path.SAMPLE'));
        self::assertNull($this->input->server->get('path.another'));
    }

    public function testAll(): void
    {
        $request = new ServerRequest(
            'GET',
            '',
            serverParams: ['PATH' => 'sample']
        );

        $this->container->bind(ServerRequestInterface::class, $request);

        self::assertSame([
            'PATH' => 'sample',
        ], $this->input->server->all());
    }

    public function testServerBagFetchNoFill(): void
    {
        $request = new ServerRequest(
            'GET',
            '',
            serverParams: ['PATH' => 'sample']
        );

        $this->container->bind(ServerRequestInterface::class, $request);

        self::assertSame([
            'PATH' => 'sample',
        ], $this->input->server->all());

        self::assertSame([
            'PATH' => 'sample',
        ], $this->input->server->fetch(['path']));
    }

    public function testServerBagFetchAndFill(): void
    {
        $request = new ServerRequest(
            'GET',
            '',
            serverParams: ['PATH' => 'sample']
        );

        $this->container->bind(ServerRequestInterface::class, $request);

        self::assertSame([
            'PATH' => 'sample',
        ], $this->input->server->fetch(['path'], true, null));

        self::assertSame(['PATH' => 'sample', 'OTHER' => null], $this->input->server->fetch(['path', 'other'], true, null));
    }

    public function testServerBagCount(): void
    {
        $request = new ServerRequest(
            'GET',
            '',
            serverParams: ['PATH' => 'sample']
        );

        $this->container->bind(ServerRequestInterface::class, $request);

        self::assertCount(1, $this->input->server);
    }

    public function testServerBagArrayAccess(): void
    {
        $request = new ServerRequest(
            'GET',
            '',
            serverParams: ['PATH' => 'sample']
        );

        $this->container->bind(ServerRequestInterface::class, $request);

        self::assertSame('sample', $this->input->server['path']);
        self::assertArrayNotHasKey('other', $this->input->server);
    }

    public function testDebugInfo(): void
    {
        $request = new ServerRequest(
            'GET',
            '',
            serverParams: ['PATH' => 'sample']
        );

        $this->container->bind(ServerRequestInterface::class, $request);

        self::assertSame(['PATH' => 'sample',], $this->input->server->__debugInfo());
    }

    public function testIterator(): void
    {
        $request = new ServerRequest(
            'GET',
            '',
            serverParams: ['PATH' => 'sample']
        );

        $this->container->bind(ServerRequestInterface::class, $request);

        self::assertSame(['PATH' => 'sample',], iterator_to_array($this->input->server));
    }

    public function testSetAndExceptions(): void
    {
        $this->expectException(InputException::class);

        $request = new ServerRequest(
            'GET',
            '',
            serverParams: ['PATH' => 'sample']
        );

        $this->container->bind(ServerRequestInterface::class, $request);
        $this->input->server->offsetSet('a', 'value');
    }

    public function testUnsetAndExceptions(): void
    {
        $this->expectException(InputException::class);

        $request = new ServerRequest(
            'GET',
            '',
            serverParams: ['PATH' => 'sample']
        );

        $this->container->bind(ServerRequestInterface::class, $request);
        $this->input->server->offsetUnset('a');
    }
}
