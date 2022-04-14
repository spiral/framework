<?php

declare(strict_types=1);

namespace Spiral\Tests\Broadcasting\Driver;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Spiral\Broadcasting\Driver\LogBroadcast;

final class LogBroadcastTest extends TestCase
{
    use m\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /**
     * @var m\LegacyMockInterface|m\MockInterface|LoggerInterface
     */
    private $logger;
    private LogBroadcast $driver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->driver = new LogBroadcast(
            $this->logger = m::mock(LoggerInterface::class)
        );
    }

    public function testAuthorize(): void
    {
        $this->assertTrue(
            $this->driver->authorize(m::mock(ServerRequestInterface::class))
        );
    }

    public function testPublishMessageToTopic(): void
    {
        $this->logger->shouldReceive('info')->once()->with('Broadcasting on channels [topic] with payload: message');
        $this->driver->publish('topic', 'message');
    }

    public function testPublishMessagesToTopic(): void
    {
        $this->logger->shouldReceive('info')->once()->with('Broadcasting on channels [topic] with payload: message1');
        $this->logger->shouldReceive('info')->once()->with('Broadcasting on channels [topic] with payload: message2');

        $this->driver->publish('topic', ['message1', 'message2']);
    }

    public function testPublishMessageToTopics(): void
    {
        $this->logger->shouldReceive('info')->once()->with('Broadcasting on channels [topic1, topic2] with payload: message');

        $this->driver->publish(['topic1', 'topic2'], 'message');
    }

    public function testPublishMessagesToTopics(): void
    {
        $this->logger->shouldReceive('info')->once()->with('Broadcasting on channels [topic1, topic2] with payload: message1');
        $this->logger->shouldReceive('info')->once()->with('Broadcasting on channels [topic1, topic2] with payload: message2');

        $this->driver->publish(['topic1', 'topic2'], ['message1', 'message2']);
    }
}
