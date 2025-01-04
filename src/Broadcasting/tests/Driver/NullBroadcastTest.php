<?php

declare(strict_types=1);

namespace Spiral\Tests\Broadcasting\Driver;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Spiral\Broadcasting\Driver\NullBroadcast;

final class NullBroadcastTest extends TestCase
{
    use m\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private NullBroadcast $driver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->driver = new NullBroadcast();
    }

    public function testPublishMessageToTopic(): void
    {
        $this->driver->publish('topic', 'message');
        self::assertTrue(true);
    }
}
