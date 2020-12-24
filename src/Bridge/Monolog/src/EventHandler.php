<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Monolog;

use Monolog\Handler\AbstractHandler;
use Monolog\Logger;
use Spiral\Logger\Event\LogEvent;
use Spiral\Logger\ListenerRegistryInterface;

final class EventHandler extends AbstractHandler
{
    /** @var ListenerRegistryInterface */
    private $listenerRegistry;

    /**
     * @param ListenerRegistryInterface $listenerRegistry
     * @param int                       $level
     * @param bool                      $bubble
     */
    public function __construct(
        ListenerRegistryInterface $listenerRegistry,
        int $level = Logger::DEBUG,
        bool $bubble = true
    ) {
        $this->listenerRegistry = $listenerRegistry;

        parent::__construct($level, $bubble);
    }

    /**
     * @param array $record
     * @return bool
     */
    public function handle(array $record): bool
    {
        $e = new LogEvent(
            $record['datetime'],
            $record['channel'],
            strtolower(Logger::getLevelName($record['level'])),
            $record['message'],
            $record['context']
        );

        foreach ($this->listenerRegistry->getListeners() as $listener) {
            call_user_func($listener, $e);
        }

        return true;
    }
}
