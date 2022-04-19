<?php

declare(strict_types=1);

namespace Spiral\Broadcasting\Driver;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

final class LogBroadcast extends AbstractBroadcast
{
    private LoggerInterface $logger;
    private string $level;

    public function __construct(LoggerInterface $logger, string $level = LogLevel::INFO)
    {
        $this->logger = $logger;
        $this->level = $level;
    }

    public function publish($topics, $messages): void
    {
        $topics = implode(', ', $this->formatTopics($this->toArray($topics)));

        /** @var string $message */
        foreach ($this->toArray($messages) as $message) {
            assert(\is_string($message), 'Message argument must be a type of string');
            $this->logger->log($this->level, 'Broadcasting on channels [' . $topics . '] with payload: ' . $message);
        }
    }
}
