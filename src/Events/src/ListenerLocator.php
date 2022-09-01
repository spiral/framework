<?php

declare(strict_types=1);

namespace Spiral\Events;

use Spiral\Attributes\ReaderInterface;
use Spiral\Events\Attribute\Listener;
use Spiral\Events\Config\EventListener;
use Spiral\Tokenizer\ScopedClassesInterface;

final class ListenerLocator implements ListenerLocatorInterface
{
    public function __construct(
        private readonly ScopedClassesInterface $locator,
        private readonly ReaderInterface $reader
    ) {
    }

    /**
     * @psalm-return \Generator<EventListener>
     */
    public function findListeners(): \Generator
    {
        foreach ($this->locator->getScopedClasses('listeners') as $class) {
            $listenerAttr = $this->reader->firstClassMetadata($class, Listener::class);

            if ($listenerAttr !== null) {
                yield new EventListener(
                    listener: $class->getName(),
                    event: $listenerAttr->event,
                    method: $listenerAttr->method,
                    priority: $listenerAttr->priority
                );
            }

            foreach ($class->getMethods() as $method) {
                $listenerAttr = $this->reader->firstFunctionMetadata($method, Listener::class);

                if ($listenerAttr !== null) {
                    yield new EventListener(
                        listener: $class->getName(),
                        event: $listenerAttr->event,
                        method: $method->getName(),
                        priority: $listenerAttr->priority
                    );
                }
            }
        }
    }
}
