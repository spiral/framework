<?php

declare(strict_types=1);

namespace Spiral\Tests\Framework\Http;

use Nyholm\Psr7\Factory\Psr17Factory;
use Spiral\Framework\Spiral;
use Spiral\Testing\Attribute\TestScope;
use Spiral\Tests\Framework\HttpTestCase;

#[TestScope(Spiral::Http)]
final class ControllerTest extends HttpTestCase
{
    public function testIndexAction(): void
    {
        $this->fakeHttp()->get('/index')->assertBodySame('Hello, Dave.');
        $this->fakeHttp()->get('/index/Antony')->assertBodySame('Hello, Antony.');
    }

    public function testRouteJson(): void
    {
        $this->fakeHttp()->get('/route')->assertBodySame('{"action":"route","name":"Dave"}');
    }

    public function test404(): void
    {
        $this->fakeHttp()->get('/undefined')->assertNotFound();
    }

    public function testPayloadAction(): void
    {
        $factory = new Psr17Factory();

        $this->fakeHttp()->post(
            uri: '/payload',
            data: $factory->createStream('{"a":"b"}'),
            headers: ['Content-Type' => 'application/json;charset=UTF-8;']
        )->assertBodySame('{"a":"b"}')
            ->assertStatus(200);
    }

    public function testPayloadWithCustomJsonHeader(): void
    {
        $factory = new Psr17Factory();

        $this->fakeHttp()->post(
            uri: '/payload',
            data: $factory->createStream('{"a":"b"}'),
            headers: ['Content-Type' => 'application/vnd.api+json;charset=UTF-8;']
        )->assertBodySame('{"a":"b"}')
            ->assertStatus(200);
    }

    public function testPayloadActionBad(): void
    {
        $factory = new Psr17Factory();

        $this->fakeHttp()->post(
            uri: '/payload',
            data: $factory->createStream('{"a":"b"'),
            headers: ['Content-Type' => 'application/json;charset=UTF-8;']
        )
            ->assertStatus(400);
    }

    public function test500(): void
    {
        $this->fakeHttp()->get('/error')->assertStatus(500);
    }
}
