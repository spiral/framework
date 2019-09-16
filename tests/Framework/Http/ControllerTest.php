<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Framework\Http;

use Spiral\Framework\HttpTest;
use Spiral\Http\Diactoros\StreamFactory;

class ControllerTest extends HttpTest
{
    public function testIndexAction()
    {
        $this->assertSame('Hello, Dave.', (string)$this->get('/index')->getBody());
        $this->assertSame('Hello, Antony.', (string)$this->get('/index/Antony')->getBody());
    }

    public function testRouteJson()
    {
        $this->assertSame('{"action":"route","name":"Dave"}', (string)$this->get('/route')->getBody());
    }

    public function test404()
    {
        $this->assertSame('404', (string)$this->get('/undefined')->getStatusCode());
    }

    public function testPayloadAction()
    {
        $factory = new StreamFactory();

        $response = $this->http->handle($this->request('/payload', 'POST', [], [
            'Content-Type' => 'application/json;charset=UTF-8;'
        ], [])->withBody($factory->createStream('{"a":"b"}')));

        $this->assertSame('{"a":"b"}', (string)$response->getBody());
    }

    public function testPayloadActionBad()
    {
        $factory = new StreamFactory();

        $response = $this->http->handle($this->request('/payload', 'POST', [], [
            'Content-Type' => 'application/json;charset=UTF-8;'
        ], [])->withBody($factory->createStream('{"a":"b"')));

        $this->assertSame(400, $response->getStatusCode());
    }

    public function test500()
    {
        $this->assertSame('500', (string)$this->get('/error')->getStatusCode());
    }
}
