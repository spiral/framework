<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Http;

use Psr\Http\Message\ServerRequestInterface;
use Spiral\Http\Request\Bags\FilesBag;
use Spiral\Http\Request\Bags\HeadersBag;
use Spiral\Http\Request\Bags\InputBag;
use Spiral\Http\Request\Bags\ServerBag;
use Spiral\Http\Request\InputInterface;
use Zend\Diactoros\ServerRequest;

class InputManagerTest extends HttpTest
{
    public function testCreateOutsideOfScope()
    {
        $this->assertInstanceOf(InputInterface::class, $this->input);
    }

    /**
     * @expectedException \Spiral\Core\Exceptions\ScopeException
     */
    public function testAccessRequestOutsideOfScope()
    {
        $this->assertInstanceOf(InputInterface::class, $this->input);
        $this->input->request();
    }

    public function testUri()
    {
        $request = new ServerRequest([], [], 'http://domain.com/hello-world');
        $this->container->bind(ServerRequestInterface::class, $request);

        $this->assertSame('/hello-world', $this->input->path());

        $request = new ServerRequest([], [], 'http://domain.com/new-one');
        $this->container->bind(ServerRequestInterface::class, $request);

        $this->assertSame('/new-one', $this->input->path());

        $request = new ServerRequest([], [], '');
        $this->container->bind(ServerRequestInterface::class, $request);

        $this->assertSame('/', $this->input->path());
    }

    public function testMethod()
    {
        $request = new ServerRequest([], [], 'http://domain.com/hello-world', 'GET');
        $this->container->bind(ServerRequestInterface::class, $request);

        $this->assertSame('GET', $this->input->method());

        $request = new ServerRequest([], [], 'http://domain.com/hello-world', 'POST');
        $this->container->bind(ServerRequestInterface::class, $request);

        $this->assertSame('POST', $this->input->method());

        //case fixing
        $request = new ServerRequest([], [], 'http://domain.com/hello-world', 'put');
        $this->container->bind(ServerRequestInterface::class, $request);

        $this->assertSame('PUT', $this->input->method());
    }

    public function testIsSecure()
    {
        $request = new ServerRequest([], [], 'http://domain.com/hello-world', 'GET');
        $this->container->bind(ServerRequestInterface::class, $request);

        $this->assertFalse($this->input->isSecure());

        $request = new ServerRequest([], [], 'https://domain.com/hello-world', 'POST');
        $this->container->bind(ServerRequestInterface::class, $request);

        $this->assertTrue($this->input->isSecure());
    }

    public function testIsAjax()
    {
        $request = new ServerRequest(
            [],
            [],
            'http://domain.com/hello-world',
            'GET',
            'php://input',
            [
            ]
        );
        $this->container->bind(ServerRequestInterface::class, $request);

        $this->assertFalse($this->input->isAjax());

        $request = new ServerRequest(
            [],
            [],
            'http://domain.com/hello-world',
            'GET',
            'php://input',
            [
                'X-Requested-With' => 'xmlhttprequest'
            ]
        );
        $this->container->bind(ServerRequestInterface::class, $request);

        $this->assertTrue($this->input->isAjax());
    }


    public function testIsJsonExcpected()
    {
        $request = new ServerRequest(
            [],
            [],
            'http://domain.com/hello-world',
            'GET',
            'php://input',
            [
            ]
        );
        $this->container->bind(ServerRequestInterface::class, $request);

        $this->assertFalse($this->input->isJsonExpected());

        $request = new ServerRequest(
            [],
            [],
            'http://domain.com/hello-world',
            'GET',
            'php://input',
            [
                'Accept' => 'application/json'
            ]
        );
        $this->container->bind(ServerRequestInterface::class, $request);

        $this->assertTrue($this->input->isJsonExpected());
    }

    public function testRemoteIP()
    {
        $request = new ServerRequest(
            [
                'REMOTE_ADDR' => '127.0.0.1'
            ],
            [],
            'http://domain.com/hello-world',
            'GET',
            'php://input',
            [
            ]
        );
        $this->container->bind(ServerRequestInterface::class, $request);

        $this->assertSame('127.0.0.1', $this->input->remoteAddress());

        $request = new ServerRequest(
            [
                'REMOTE_ADDR' => null
            ],
            [],
            'http://domain.com/hello-world',
            'GET',
            'php://input',
            [
                'Accept' => 'application/json'
            ]
        );
        $this->container->bind(ServerRequestInterface::class, $request);

        $this->assertTrue($this->input->isJsonExpected());

        $this->assertSame(null, $this->input->remoteAddress());
    }

    public function testGetBag()
    {
        $request = new ServerRequest(
            [
            ],
            [],
            'http://domain.com/hello-world',
            'GET',
            'php://input',
            [
            ]
        );
        $this->container->bind(ServerRequestInterface::class, $request);

        $this->assertInstanceOf(ServerBag::class, $this->input->server);
        $this->assertInstanceOf(InputBag::class, $this->input->attributes);
        $this->assertInstanceOf(InputBag::class, $this->input->data);
        $this->assertInstanceOf(InputBag::class, $this->input->cookies);
        $this->assertInstanceOf(InputBag::class, $this->input->query);
        $this->assertInstanceOf(FilesBag::class, $this->input->files);
        $this->assertInstanceOf(HeadersBag::class, $this->input->headers);
    }

    /**
     * @expectedException \Spiral\Http\Exceptions\InputException
     */
    public function testWrongBad()
    {
        $request = new ServerRequest(
            [
            ],
            [],
            'http://domain.com/hello-world',
            'GET',
            'php://input',
            [
            ]
        );

        $this->container->bind(ServerRequestInterface::class, $request);
        $this->input->invalid;
    }
}