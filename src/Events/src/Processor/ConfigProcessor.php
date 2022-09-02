<?php

declare(strict_types=1);

namespace Spiral\Events\Processor;

use Spiral\Events\Config\EventsConfig;
use Spiral\Events\ListenerFactory;
use Spiral\Events\ListenerRegistryInterface;

final class ConfigProcessor extends AbstractProcessor
{
    public function __construct(
        private readonly EventsConfig $config,
        private readonly ListenerRegistryInterface $registry,
        private readonly ListenerFactory $factory
    ) {
    }

    public function process(): void
    {
        foreach ($this->config->getListeners() as $event => $eventListeners) {
            foreach ($eventListeners as $listener) {
                $method = $this->getMethod($listener->listener, $listener->method);

                $this->registry->addListener(
                    event: $event,
                    listener: $this->factory->create($listener->listener, $method->getName()),
                    priority: $listener->priority
                );
            }
        }
    }
}
