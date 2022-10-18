<?php

declare(strict_types=1);

namespace Spiral\Events;

use Psr\EventDispatcher\EventDispatcherInterface;
use Spiral\Core\CoreInterface;

final class EventDispatcher implements EventDispatcherInterface
{
    public function __construct(
        private readonly CoreInterface $core
    ) {
    }

    public function dispatch(object $event): object
    {
        return $this->core->callAction($event::class, 'dispatch', ['event' => $event]);
    }
}
