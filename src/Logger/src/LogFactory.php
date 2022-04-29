<?php

declare(strict_types=1);

namespace Spiral\Logger;

use Psr\Log\LoggerInterface;
use Spiral\Logger\Event\LogEvent;

/**
 * Routes log information to various listeners.
 */
final class LogFactory implements LogsInterface
{
    public function __construct(
        private readonly ListenerRegistryInterface $listenedRegistry
    ) {
    }

    public function getLogger(string $channel): LoggerInterface
    {
        return new NullLogger([$this, 'log'], $channel);
    }

    public function log(string $channel, mixed $level, string $message, array $context = []): void
    {
        $e = new LogEvent(
            new \DateTime(),
            $channel,
            (string) $level,
            $message,
            $context
        );

        foreach ($this->listenedRegistry->getListeners() as $listener) {
            \call_user_func($listener, $e);
        }
    }
}
