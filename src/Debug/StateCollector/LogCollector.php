<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Debug\StateCollector;

use Spiral\Debug\StateCollectorInterface;
use Spiral\Debug\StateInterface;
use Spiral\Logger\Event\LogEvent;

final class LogCollector implements StateCollectorInterface
{
    /** @var LogEvent[] */
    private $logEvents = [];

    /**
     * @param LogEvent $event
     */
    public function __invoke(LogEvent $event): void
    {
        $this->logEvents[] = $event;
    }

    /**
     * @param StateInterface $state
     */
    public function populate(StateInterface $state): void
    {
        $state->addLogEvent(...$this->logEvents);
    }

    /**
     * Reset the collector state.
     */
    public function reset(): void
    {
        $this->logEvents = [];
    }
}
