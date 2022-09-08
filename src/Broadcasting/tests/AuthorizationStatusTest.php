<?php

declare(strict_types=1);

namespace Spiral\Tests\Broadcasting;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Spiral\Broadcasting\AuthorizationStatus;

final class AuthorizationStatusTest extends TestCase
{
    public function testIsSuccessful(): void
    {
        $false = new AuthorizationStatus(false, []);
        $this->assertFalse($false->success);

        $true = new AuthorizationStatus(true, []);
        $this->assertTrue($true->success);
    }

    public function testGetsTopics(): void
    {
        $status = new AuthorizationStatus(false, $topics = ['topic1', 'topic2']);
        $this->assertSame($topics, $status->topics);
    }

    public function testGetsAttributes(): void
    {
        $status = new AuthorizationStatus(false, ['topic1'], $attributes = ['foo' => 'bar']);
        $this->assertSame($attributes, $status->attributes);
    }

    public function testGetsNullResponse(): void
    {
        $status = new AuthorizationStatus(false, ['topic1'], ['foo' => 'bar']);
        $this->assertNull($status->response);
        $this->assertFalse($status->hasResponse());
    }

    public function testGetsResponse(): void
    {
        $status = new AuthorizationStatus(
            false,
            ['topic1'],
            ['foo' => 'bar'],
            $response = m::mock(ResponseInterface::class)
        );

        $this->assertSame($response, $status->response);
        $this->assertTrue($status->hasResponse());
    }

    public function testWith(): void
    {
        $status = new AuthorizationStatus(success: false, topics: null);

        $this->assertNull($status->response);
        $newStatus = $status->with(response: $response = m::mock(ResponseInterface::class));
        $this->assertSame($response, $newStatus->response);

        $this->assertFalse($status->success);
        $newStatus = $status->with(success: true);
        $this->assertTrue($newStatus->success);

        $this->assertNull($status->topics);
        $newStatus = $status->with(topics: $topics = ['foo', 'bar']);
        $this->assertSame($topics, $newStatus->topics);

        $this->assertSame([], $status->attributes);
        $newStatus = $status->with(attributes: $attributes = ['foo', 'bar']);
        $this->assertSame($attributes, $newStatus->attributes);
    }
}
