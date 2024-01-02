<?php

declare(strict_types=1);

namespace Spiral\Monolog;

use Monolog\Handler\AbstractHandler;
use Monolog\Logger;
use Monolog\LogRecord;
use Spiral\Logger\Event\LogEvent;
use Spiral\Logger\ListenerRegistryInterface;

final class EventHandler extends AbstractHandler
{
    public function __construct(
        private readonly ListenerRegistryInterface $listenerRegistry,
        int $level = Logger::DEBUG,
        bool $bubble = true
    ) {
        parent::__construct($level, $bubble);
    }

    public function handle(array|LogRecord $record): bool
    {
        $e = new LogEvent(
            $record['datetime'],
            $record['channel'],
            \strtolower(Logger::getLevelName($record['level'])),
            $record['message'],
            $record['context']
        );

        foreach ($this->listenerRegistry->getListeners() as $listener) {
            $listener($e);
        }

        return false === $this->bubble;
    }
}
