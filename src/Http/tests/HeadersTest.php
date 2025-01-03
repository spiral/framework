<?php

declare(strict_types=1);

namespace Spiral\Tests\Http;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Core\Container;
use Spiral\Http\Request\InputManager;
use Nyholm\Psr7\ServerRequest;

class HeadersTest extends TestCase
{
    private Container $container;

    private InputManager $input;

    public function setUp(): void
    {
        $this->container = new Container();
        $this->input = new InputManager($this->container);
    }

    public function testShortcut(): void
    {
        $request = new ServerRequest('GET', '');

        $request = $request->withAddedHeader('Path', 'value');
        $this->container->bind(ServerRequestInterface::class, $request);

        $this->assertSame('value', $this->input->header('path'));
    }

    public function testHas(): void
    {
        $request = new ServerRequest('GET', '');

        $request = $request->withAddedHeader('Path', 'value');
        $this->container->bind(ServerRequestInterface::class, $request);

        $this->assertTrue($this->input->headers->has('path'));
        $this->assertTrue($this->input->headers->has('Path'));
    }

    public function testFetch(): void
    {
        $request = new ServerRequest('GET', '');

        $request = $request->withAddedHeader('Path', 'value');
        $request = $request->withAddedHeader('Path', 'value2');
        $this->container->bind(ServerRequestInterface::class, $request);

        $this->assertSame([
            'Path' => 'value,value2'
        ], $this->input->headers->fetch(['path']));
    }

    public function testFetchNoImplode(): void
    {
        $request = new ServerRequest('GET', '');

        $request = $request->withAddedHeader('Path', 'value');
        $request = $request->withAddedHeader('Path', 'value2');
        $this->container->bind(ServerRequestInterface::class, $request);

        $this->assertSame([
            'Path' => ['value', 'value2']
        ], $this->input->headers->fetch(['path'], false, true, null));

        $this->assertSame(
            ['value', 'value2'],
            $this->input->headers->get('path', null, false)
        );
    }
}
