<?php

declare(strict_types=1);

namespace Spiral\Events\Processor;

use Spiral\Events\ListenerFactoryInterface;
use Spiral\Events\ListenerLocatorInterface;
use Spiral\Events\ListenerRegistryInterface;

final class AttributeProcessor extends AbstractProcessor
{
    public function __construct(
        private readonly ListenerLocatorInterface $locator,
        private readonly ListenerFactoryInterface $factory,
        private readonly ?ListenerRegistryInterface $registry = null,
    ) {
    }

    public function process(): void
    {
        if ($this->registry === null) {
            return;
        }
        foreach ($this->locator->findListeners() as $listener => $attr) {
            $method = $this->getMethod($listener, $attr->method ?? '__invoke');

            $events = (array)($attr->event ?? $this->getEventFromTypeDeclaration($method));
            foreach ($events as $event) {
                $this->registry->addListener(
                    event: $event,
                    listener: $this->factory->create($listener, $method->getName()),
                    priority: $attr->priority
                );
            }
        }
    }
}
