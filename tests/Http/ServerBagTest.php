<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Http;

use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\ServerRequest;

class ServerBagTest extends HttpTest
{
    public function testShortcut()
    {
        $request = new ServerRequest(
            ['PATH' => 'sample']
        );

        $this->container->bind(ServerRequestInterface::class, $request);

        $this->assertSame('sample', $this->input->server('path'));
    }

    public function testHas()
    {
        $request = new ServerRequest(
            ['PATH' => 'sample']
        );

        $this->container->bind(ServerRequestInterface::class, $request);

        $this->assertTrue($this->input->server->has('path'));
        $this->assertFalse($this->input->server->has('another'));
        $this->assertTrue($this->input->server->has('path'));
    }

    public function testGet()
    {
        $request = new ServerRequest(
            ['PATH' => 'sample']
        );

        $this->container->bind(ServerRequestInterface::class, $request);

        $this->assertSame('sample', $this->input->server->get('path'));
        $this->assertSame(null, $this->input->server->get('other'));
    }

    public function testGetDot()
    {
        $request = new ServerRequest(
            ['PATH' => ['SAMPLE' => 1]]
        );

        $this->container->bind(ServerRequestInterface::class, $request);

        $this->assertSame(1, $this->input->server->get('path.SAMPLE'));
        $this->assertSame(null, $this->input->server->get('path.another'));
    }

    public function testAll()
    {
        $request = new ServerRequest(
            ['PATH' => 'sample']
        );

        $this->container->bind(ServerRequestInterface::class, $request);

        $this->assertSame([
            'PATH' => 'sample'
        ], $this->input->server->all());
    }

    public function testServerBagFetchNoFill()
    {
        $request = new ServerRequest(
            ['PATH' => 'sample']
        );

        $this->container->bind(ServerRequestInterface::class, $request);

        $this->assertSame([
            'PATH' => 'sample'
        ], $this->input->server->all());

        $this->assertSame([
            'PATH' => 'sample'
        ], $this->input->server->fetch(['path']));
    }

    public function testServerBagFetchAndFill()
    {
        $request = new ServerRequest(
            ['PATH' => 'sample']
        );

        $this->container->bind(ServerRequestInterface::class, $request);

        $this->assertSame([
            'PATH' => 'sample'
        ], $this->input->server->fetch(['path'], true, null));

        $this->assertSame(
            ['PATH' => 'sample', 'OTHER' => null],
            $this->input->server->fetch(['path', 'other'], true, null)
        );
    }

    public function testServerBagCount()
    {
        $request = new ServerRequest(
            ['PATH' => 'sample']
        );

        $this->container->bind(ServerRequestInterface::class, $request);

        $this->assertSame(1, $this->input->server->count());
    }

    public function testServerBagArrayAccess()
    {
        $request = new ServerRequest(
            ['PATH' => 'sample']
        );

        $this->container->bind(ServerRequestInterface::class, $request);

        $this->assertSame('sample', $this->input->server['path']);
        $this->assertFalse(isset($this->input->server['other']));
    }

    public function testDebugInfo()
    {
        $request = new ServerRequest(
            ['PATH' => 'sample']
        );

        $this->container->bind(ServerRequestInterface::class, $request);

        $this->assertSame(
            ['PATH' => 'sample',],
            $this->input->server->__debugInfo()
        );
    }

    public function testIterator()
    {
        $request = new ServerRequest(
            ['PATH' => 'sample']
        );

        $this->container->bind(ServerRequestInterface::class, $request);

        $this->assertSame(
            ['PATH' => 'sample',],
            iterator_to_array($this->input->server)
        );
    }

    /**
     * @expectedException \Spiral\Http\Exceptions\InputException
     */
    public function testSetAndExceptions()
    {
        $request = new ServerRequest(
            ['PATH' => 'sample']
        );

        $this->container->bind(ServerRequestInterface::class, $request);
        $this->input->server->offsetSet('a', 'value');
    }

    /**
     * @expectedException \Spiral\Http\Exceptions\InputException
     */
    public function testUnsetAndExceptions()
    {
        $request = new ServerRequest(
            ['PATH' => 'sample']
        );

        $this->container->bind(ServerRequestInterface::class, $request);
        $this->input->server->offsetUnset('a');
    }
}