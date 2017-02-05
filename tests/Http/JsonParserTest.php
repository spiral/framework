<?php
/**
 * spiral
 *
 * @author    Wolfy-J
 */

namespace Spiral\Tests\Http;

use Spiral\Core\ConfiguratorInterface;
use Spiral\Http\Configs\HttpConfig;
use Spiral\Http\Middlewares\JsonParser;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\Stream;
use function GuzzleHttp\json_encode;

class JsonParserTest extends HttpTest
{
    public function setUp()
    {
        parent::setUp();

        $config = $this->container->get(ConfiguratorInterface::class)->getConfig(HttpConfig::CONFIG);

        //Flush default middlewares
        $config['middlewares'] = [];

        $this->container->bind(HttpConfig::class, new HttpConfig($config));
    }

    public function testNoActions()
    {
        $this->http->pushMiddleware(new JsonParser());

        $this->http->setEndpoint(function () {
            return json_encode($this->request->getParsedBody());
        });

        $result = $this->get('/');

        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('null', $result->getBody()->__toString());
    }

    public function testExistedBodyNoHeader()
    {
        $this->http->pushMiddleware(new JsonParser());

        $this->http->setEndpoint(function () {
            return json_encode($this->request->getParsedBody());
        });

        $result = $this->post('/', [
            'a' => 123
        ]);

        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame(json_encode(['a' => 123]), $result->getBody()->__toString());
    }

    public function testParseBodyNoHeader()
    {
        $this->http->pushMiddleware(new JsonParser());

        $this->http->setEndpoint(function () {
            return json_encode($this->request->getParsedBody());
        });

        $body = new Stream('php://memory', 'wr');
        $body->write('hello world');

        $result = $this->http->perform(new ServerRequest(
            [],
            [],
            '/',
            'POST',
            $body
        ));

        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('null', $result->getBody()->__toString());
    }

    public function testParseBody()
    {
        $this->http->pushMiddleware(new JsonParser());

        $this->http->setEndpoint(function () {
            return json_encode($this->request->getParsedBody());
        });

        $body = new Stream('php://memory', 'wr');
        $body->write(json_encode(['a' => 123]));

        $result = $this->http->perform(new ServerRequest(
            [],
            [],
            '/',
            'POST',
            $body,
            [
                'Content-Type' => 'application/json'
            ]
        ));

        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame(json_encode(['a' => 123]), $result->getBody()->__toString());
    }

    public function testParseBodyError()
    {
        $this->http->pushMiddleware(new JsonParser());

        $this->http->setEndpoint(function () {
            return json_encode($this->request->getParsedBody());
        });

        $body = new Stream('php://memory', 'wr');
        $body->write('%^&*');

        $result = $this->http->perform(new ServerRequest(
            [],
            [],
            '/',
            'POST',
            $body,
            [
                'Content-Type' => 'application/json'
            ]
        ));

        $this->assertSame(400, $result->getStatusCode());
    }
}