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
        self::assertFalse($false->success);

        $true = new AuthorizationStatus(true, []);
        self::assertTrue($true->success);
    }

    public function testGetsTopics(): void
    {
        $status = new AuthorizationStatus(false, $topics = ['topic1', 'topic2']);
        self::assertSame($topics, $status->topics);
    }

    public function testGetsAttributes(): void
    {
        $status = new AuthorizationStatus(false, ['topic1'], $attributes = ['foo' => 'bar']);
        self::assertSame($attributes, $status->attributes);
    }

    public function testGetsNullResponse(): void
    {
        $status = new AuthorizationStatus(false, ['topic1'], ['foo' => 'bar']);
        self::assertNull($status->response);
        self::assertFalse($status->hasResponse());
    }

    public function testGetsResponse(): void
    {
        $status = new AuthorizationStatus(
            false,
            ['topic1'],
            ['foo' => 'bar'],
            $response = m::mock(ResponseInterface::class)
        );

        self::assertSame($response, $status->response);
        self::assertTrue($status->hasResponse());
    }

    public function testWith(): void
    {
        $status = new AuthorizationStatus(success: false, topics: null);

        self::assertNull($status->response);
        $newStatus = $status->with(response: $response = m::mock(ResponseInterface::class));
        self::assertSame($response, $newStatus->response);

        self::assertFalse($status->success);
        $newStatus = $status->with(success: true);
        self::assertTrue($newStatus->success);

        self::assertNull($status->topics);
        $newStatus = $status->with(topics: $topics = ['foo', 'bar']);
        self::assertSame($topics, $newStatus->topics);

        self::assertSame([], $status->attributes);
        $newStatus = $status->with(attributes: $attributes = ['foo', 'bar']);
        self::assertSame($attributes, $newStatus->attributes);
    }
}
