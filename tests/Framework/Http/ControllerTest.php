<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Framework\Http;

use Nyholm\Psr7\Factory\Psr17Factory;
use Spiral\Tests\Framework\HttpTest;

class ControllerTest extends HttpTest
{
    public function testIndexAction(): void
    {
        $this->assertSame('Hello, Dave.', (string)$this->get('/index')->getBody());
        $this->assertSame('Hello, Antony.', (string)$this->get('/index/Antony')->getBody());
    }

    public function testRouteJson(): void
    {
        $this->assertSame('{"action":"route","name":"Dave"}', (string)$this->get('/route')->getBody());
    }

    public function test404(): void
    {
        $this->assertSame('404', (string)$this->get('/undefined')->getStatusCode());
    }

    public function testPayloadAction(): void
    {
        $factory = new Psr17Factory();

        $response = $this->http->handle($this->request('/payload', 'POST', [], [
            'Content-Type' => 'application/json;charset=UTF-8;'
        ], [])->withBody($factory->createStream('{"a":"b"}')));

        $this->assertSame('{"a":"b"}', (string)$response->getBody());
    }

    public function testPayloadWithCustomJsonHeader(): void
    {
        $factory = new Psr17Factory();

        $response = $this->http->handle($this->request('/payload', 'POST', [], [
            'Content-Type' => 'application/vnd.api+json;charset=UTF-8;'
        ], [])->withBody($factory->createStream('{"a":"b"}')));

        $this->assertSame('{"a":"b"}', (string)$response->getBody());
    }

    public function testPayloadActionBad(): void
    {
        $factory = new Psr17Factory();

        $response = $this->http->handle($this->request('/payload', 'POST', [], [
            'Content-Type' => 'application/json;charset=UTF-8;'
        ], [])->withBody($factory->createStream('{"a":"b"')));

        $this->assertSame(400, $response->getStatusCode());
    }

    public function test500(): void
    {
        $this->assertSame('500', (string)$this->get('/error')->getStatusCode());
    }
}
