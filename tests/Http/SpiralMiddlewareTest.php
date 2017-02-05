<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Http;

use Spiral\Core\ConfiguratorInterface;
use Spiral\Http\Configs\HttpConfig;
use Spiral\Http\Exceptions\ClientExceptions\NotFoundException;
use Spiral\Http\MiddlewarePipeline;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

class SpiralMiddlewareTest extends HttpTest
{
    public function setUp()
    {
        parent::setUp();

        $config = $this->container->get(ConfiguratorInterface::class)->getConfig(HttpConfig::CONFIG);

        //Flush default middlewares
        $config['middlewares'] = [];

        $this->container->bind(HttpConfig::class, new HttpConfig($config));
    }


    public function testHttpAsMiddlewareButNothingFound()
    {
        $pipeline = new MiddlewarePipeline([], $this->container);

        $pipeline->target(function ($req, $res) {
            $res->getBody()->write('default response');

            return $res;
        });

        $response = $pipeline->run(
            new ServerRequest(),
            new Response()
        );

        $this->assertSame('default response', $response->getBody()->__toString());
    }

    public function testHttpWithHttpEndpoint()
    {
        $pipeline = new MiddlewarePipeline([], $this->container);

        $pipeline->pushMiddleware($this->http);

        $this->http->setEndpoint(function ($req, $res) {
            $res->getBody()->write('http response');

            return $res;
        });

        $pipeline->target(function ($req, $res) {
            $res->getBody()->write('default response');

            return $res;
        });

        $response = $pipeline->run(
            new ServerRequest(),
            new Response()
        );

        $this->assertSame('http response', $response->getBody()->__toString());
    }

    public function testHttpWithHttpEndpointNotFound()
    {
        $pipeline = new MiddlewarePipeline([], $this->container);

        $pipeline->pushMiddleware($this->http);

        $this->http->setEndpoint(function ($req, $res) {
            throw new NotFoundException();
            $res->getBody()->write('http response');

            return $res;
        });

        $pipeline->target(function ($req, $res) {
            $res->getBody()->write('default response');

            return $res;
        });

        $response = $pipeline->run(
            new ServerRequest(),
            new Response()
        );

        $this->assertSame('default response', $response->getBody()->__toString());
    }
}