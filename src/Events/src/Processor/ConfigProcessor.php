<?php

declare(strict_types=1);

namespace Spiral\Events\Processor;

use Spiral\Core\FactoryInterface;
use Spiral\Events\Config\EventsConfig;
use Spiral\Events\ListenerRegistryInterface;

final class ConfigProcessor extends AbstractProcessor
{
    public function __construct(
        private readonly EventsConfig $config,
        private readonly ListenerRegistryInterface $registry,
        private readonly FactoryInterface $factory
    ) {
    }

    public function process(): void
    {
        foreach ($this->config->getListeners() as $listener) {
            $method = $this->getMethod($listener->listener, $listener->method);

            $this->registry->addListener(
                event: !empty($listener->event) ? $listener->event : $this->getEventFromTypeDeclaration($method),
                listener: [$this->factory->make($listener->listener), $method->getName()],
                priority: $listener->priority
            );
        }
    }
}
