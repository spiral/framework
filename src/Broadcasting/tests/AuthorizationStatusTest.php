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
        $this->assertFalse($false->isSuccessful());

        $true = new AuthorizationStatus(true, []);
        $this->assertTrue($true->isSuccessful());
    }

    public function testGetsTopics(): void
    {
        $status = new AuthorizationStatus(false, $topics = ['topic1', 'topic2']);
        $this->assertSame($topics, $status->getTopics());
    }

    public function testGetsAttributes(): void
    {
        $status = new AuthorizationStatus(false, ['topic1'], $attributes = ['foo' => 'bar']);
        $this->assertSame($attributes, $status->getAttributes());
    }

    public function testGetsNullResponse(): void
    {
        $status = new AuthorizationStatus(false, ['topic1'], ['foo' => 'bar']);
        $this->assertNull($status->getResponse());
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

        $this->assertSame($response, $status->getResponse());
        $this->assertTrue($status->hasResponse());
    }
}
