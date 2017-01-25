<?php
/**
 * Spiral Framework
 *
 * @license   MIT
 * @author    Dmitry Mironov <dmitry.mironov@spiralscout.com>
 */

namespace Spiral\Tests\Http;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Spiral\Core\Containers\SpiralContainer;
use Spiral\Http\HttpCore;
use Spiral\Tests\Core\Fixtures\SharedComponent;
use Zend\Diactoros\Response as ZendResponse;
use Zend\Diactoros\ServerRequest as ZendRequest;

class CoreTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Spiral\Core\ContainerInterface
     */
    private $container;

    /**
     * @var ZendRequest
     */
    private $request;

    /**
     * @var ZendResponse
     */
    private $response;

    protected function setUp()
    {
        $this->container = new SpiralContainer();
        $this->request = new ZendRequest();
        $this->response = new ZendResponse();
    }

    public function testInvoke()
    {
        $core = new HttpCore(null, [], $this->container);
        $core->setEndpoint(function (Request $request, Response $response) {
            return $response->withStatus(300);
        });

        $response = $core($this->request, $this->response);
        $this->assertEquals(300, $response->getStatusCode());
    }

    public function testPerform()
    {
        // scoping
        $aContainer = new SpiralContainer();
        SharedComponent::shareContainer($aContainer);

        $core = new HttpCore(null, [], $this->container);
        $core->setEndpoint(function (Request $request, Response $response) {
            return $response->withStatus(300);
        });

        $response = $core->perform($this->request, $this->response);
        $this->assertEquals(300, $response->getStatusCode());

        $this->assertSame($aContainer, SharedComponent::shareContainer(null));
    }

    public function testPerformNoResponse()
    {
        $core = new HttpCore(null, [], $this->container);
        $core->setEndpoint(function (Request $request, Response $response) {
            return $response->withStatus(300);
        });

        $response = $core->perform($this->request);
        $this->assertEquals(300, $response->getStatusCode());
    }

    public function testPerformClassEndpoint()
    {
        $this->container->bind('InvokableClassName', new class
            {
                function __invoke(Request $request, Response $response)
                {
                    return $response->withStatus(300);
                }
            }
        );

        $core = new HttpCore(null, [], $this->container);
        $core->setEndpoint('InvokableClassName');

        $response = $core->perform($this->request, $this->response);
        $this->assertEquals(300, $response->getStatusCode());
    }

    /**
     * @expectedException \Spiral\Http\Exceptions\HttpException
     * @expectedExceptionMessage Unable to execute request without destination endpoint
     */
    public function testPerformNoEndpoint()
    {
        $core = new HttpCore(null, [], $this->container);

        $core->perform($this->request, $this->response);
    }

    public function testDispatch()
    {
        $emitter = new class implements ZendResponse\EmitterInterface
        {
            public function emit(Response $response, $maxBufferLevel = null)
            {
                CoreTest::assertEquals(300, $response->getStatusCode());
                CoreTest::assertEquals(ob_get_level(), $maxBufferLevel);
            }
        };

        $core = new HttpCore(null, [], $this->container);
        $core->setEmitter($emitter);
        $core->setEndpoint(function (Request $request, Response $response) {
            return $response->withStatus(300);
        });

        $response = $core->perform($this->request, $this->response);
        $core->dispatch($response);
    }
}