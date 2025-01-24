<?php

declare(strict_types=1);

namespace Spiral\Tests\Broadcasting\Driver;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Spiral\Broadcasting\Driver\LogBroadcast;

final class LogBroadcastTest extends TestCase
{
    use m\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /**
     * @var m\LegacyMockInterface|m\MockInterface|LoggerInterface
     */
    private $logger;

    private LogBroadcast $driver;

    public function testPublishMessageToTopicWithLoglevel(): void
    {
        $driver = new LogBroadcast(
            $logger = m::mock(LoggerInterface::class),
            LogLevel::DEBUG,
        );

        $logger->shouldReceive('log')->once()
            ->with(LogLevel::DEBUG, 'Broadcasting on channels [topic] with payload: message');
        $driver->publish('topic', 'message');
    }

    public function testPublishMessageToTopic(): void
    {
        $this->logger->shouldReceive('log')->once()
            ->with(LogLevel::INFO, 'Broadcasting on channels [topic] with payload: message');
        $this->driver->publish('topic', 'message');
    }

    public function testPublishMessagesToTopic(): void
    {
        $this->logger->shouldReceive('log')->once()
            ->with(LogLevel::INFO, 'Broadcasting on channels [topic] with payload: message1');
        $this->logger->shouldReceive('log')->once()
            ->with(LogLevel::INFO, 'Broadcasting on channels [topic] with payload: message2');

        $this->driver->publish('topic', ['message1', 'message2']);
    }

    public function testPublishMessageToTopics(): void
    {
        $this->logger->shouldReceive('log')->once()
            ->with(LogLevel::INFO, 'Broadcasting on channels [topic1, topic2] with payload: message');

        $this->driver->publish(['topic1', 'topic2'], 'message');
    }

    public function testPublishMessagesToTopics(): void
    {
        $this->logger->shouldReceive('log')->once()
            ->with(LogLevel::INFO, 'Broadcasting on channels [topic1, topic2] with payload: message1');
        $this->logger->shouldReceive('log')->once()
            ->with(LogLevel::INFO, 'Broadcasting on channels [topic1, topic2] with payload: message2');

        $this->driver->publish(['topic1', 'topic2'], ['message1', 'message2']);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->driver = new LogBroadcast(
            $this->logger = m::mock(LoggerInterface::class),
        );
    }
}
