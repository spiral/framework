<?php

declare(strict_types=1);

namespace Spiral\Events\Processor;

use Spiral\Core\FactoryInterface;
use Spiral\Events\ListenerLocatorInterface;
use Spiral\Events\ListenerRegistryInterface;

final class AttributeProcessor extends AbstractProcessor
{
    public function __construct(
        private readonly ListenerLocatorInterface $locator,
        private readonly ListenerRegistryInterface $registry,
        private readonly FactoryInterface $factory
    ) {
    }

    public function process(): void
    {
        foreach ($this->locator->findListeners() as $listener) {
            $method = $this->getMethod($listener->listener, $listener->method);

            $this->registry->addListener(
                event: $listener->event ?? $this->getEventFromTypeDeclaration($method),
                listener: [$this->factory->make($listener->listener), $method->getName()],
                priority: $listener->priority
            );
        }
    }
}
