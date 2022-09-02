<?php

declare(strict_types=1);

namespace Spiral\Events\Processor;

use Spiral\Events\ListenerFactory;
use Spiral\Events\ListenerLocatorInterface;
use Spiral\Events\ListenerRegistryInterface;

final class AttributeProcessor extends AbstractProcessor
{
    public function __construct(
        private readonly ListenerLocatorInterface $locator,
        private readonly ListenerRegistryInterface $registry,
        private readonly ListenerFactory $factory
    ) {
    }

    public function process(): void
    {
        foreach ($this->locator->findListeners() as $listener => $attr) {
            $method = $this->getMethod($listener, $attr->method ?? '__invoke');

            $this->registry->addListener(
                event: $attr->event ?? $this->getEventFromTypeDeclaration($method),
                listener: $this->factory->create($listener, $method->getName()),
                priority: $attr->priority
            );
        }
    }
}
