<?php

declare(strict_types=1);

namespace Spiral\Monolog;

use Monolog\Handler\AbstractHandler;
use Monolog\Logger;
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

    public function handle(array $record): bool
    {
        $e = new LogEvent(
            $record['datetime'],
            $record['channel'],
            \strtolower(Logger::getLevelName($record['level'])),
            $record['message'],
            $record['context']
        );

        foreach ($this->listenerRegistry->getListeners() as $listener) {
            \call_user_func($listener, $e);
        }

        return true;
    }
}
