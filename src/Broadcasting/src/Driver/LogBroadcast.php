<?php

declare(strict_types=1);

namespace Spiral\Broadcasting\Driver;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

final class LogBroadcast extends AbstractBroadcast
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly string $level = LogLevel::INFO
    ) {
    }

    public function publish(iterable|string|\Stringable $topics, iterable|string $messages): void
    {
        $topics = \implode(', ', $this->formatTopics($this->toArray($topics)));

        /** @var string $message */
        foreach ($this->toArray($messages) as $message) {
            \assert(\is_string($message), 'Message argument must be a type of string');
            $this->logger->log($this->level, 'Broadcasting on channels [' . $topics . '] with payload: ' . $message);
        }
    }
}
