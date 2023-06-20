<?php

declare(strict_types=1);

namespace Spiral\Tokenizer\Listener;

use Spiral\Attributes\ReaderInterface;
use Spiral\Boot\MemoryInterface;
use Spiral\Tokenizer\Attribute\AbstractTarget;
use Spiral\Tokenizer\TokenizationListenerInterface;

final class CachedInterfacesLoader implements InterfacesLoaderInterface
{
    public function __construct(
        private readonly ReaderInterface $reader,
        private readonly MemoryInterface $memory,
        private readonly InterfaceLocatorByTarget $locator,
        private readonly ListenerInvoker $invoker,
        private readonly bool $readCache = true,
    ) {
    }

    public function loadInterfaces(TokenizationListenerInterface $listener): bool
    {
        $targets = \iterator_to_array($this->parseAttributes($listener));

        // If there are no targets, then listener can't be cached.
        if ($targets === []) {
            return false;
        }

        $listenerInterfaces = [];

        // We decided to load classes for each target separately.
        // It allows us to cache classes for each target separately and if we reuse the
        // same target in multiple listeners, we will not have to load classes for the same target.
        foreach ($targets as $target) {
            $cacheKey = 'interfaces-' . $target;

            $interfaces = $this->readCache ? $this->memory->loadData($cacheKey) : null;
            if ($interfaces === null) {
                $this->memory->saveData(
                    $cacheKey,
                    $interfaces = $this->locator->getInterfaces($target),
                );
            }

            $listenerInterfaces = \array_merge($listenerInterfaces, $interfaces);
        }

        $this->invoker->invoke(
            $listener,
            \array_map(static fn (string $class) => new \ReflectionClass($class), \array_unique($listenerInterfaces)),
        );

        return true;
    }

    private function parseAttributes(TokenizationListenerInterface $listener): \Generator
    {
        $listener = new \ReflectionClass($listener);

        yield from $this->reader->getClassMetadata($listener, AbstractTarget::class);
    }
}
