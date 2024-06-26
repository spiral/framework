<?php

declare(strict_types=1);

namespace Spiral\Events;

use Psr\EventDispatcher\EventDispatcherInterface;
use Spiral\Core\CoreInterface;
use Spiral\Interceptors\Context\CallContext;
use Spiral\Interceptors\Context\Target;
use Spiral\Interceptors\HandlerInterface;

final class EventDispatcher implements EventDispatcherInterface
{
    private readonly bool $isLegacy;
    public function __construct(
        private readonly HandlerInterface|CoreInterface $core,
    ) {
        $this->isLegacy = !$core instanceof HandlerInterface;
    }

    public function dispatch(object $event): object
    {
        return $this->isLegacy
            ? $this->core->callAction(EventDispatcherInterface::class, 'dispatch', ['event' => $event])
            : $this->core->handle(new CallContext(
                Target::fromPair(EventDispatcherInterface::class, 'dispatch'),
                ['event' => $event],
            ));
    }
}
