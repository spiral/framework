<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Http;

use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\ServerRequest;

class HeaderBagTest extends HttpTest
{
    public function testShortcut()
    {
        $request = new ServerRequest();

        $request = $request->withAddedHeader('Path', 'value');
        $this->container->bind(ServerRequestInterface::class, $request);

        $this->assertSame('value', $this->input->header('path'));
    }

    public function testHas()
    {
        $request = new ServerRequest();

        $request = $request->withAddedHeader('Path', 'value');
        $this->container->bind(ServerRequestInterface::class, $request);

        $this->assertTrue($this->input->headers->has('path'));
        $this->assertTrue($this->input->headers->has('Path'));
    }

    public function testFetch()
    {
        $request = new ServerRequest();

        $request = $request->withAddedHeader('Path', 'value');
        $request = $request->withAddedHeader('Path', 'value2');
        $this->container->bind(ServerRequestInterface::class, $request);

        $this->assertSame([
            'Path' => 'value,value2'
        ], $this->input->headers->fetch(['path']));
    }

    public function testFetchNoImplode()
    {
        $request = new ServerRequest();

        $request = $request->withAddedHeader('Path', 'value');
        $request = $request->withAddedHeader('Path', 'value2');
        $this->container->bind(ServerRequestInterface::class, $request);

        $this->assertSame([
            'Path' => ['value', 'value2']
        ], $this->input->headers->fetch(['path'], false, true, false));

        $this->assertSame(
            ['value', 'value2'],
                $this->input->headers->get('path', null, false)
        );
    }
}