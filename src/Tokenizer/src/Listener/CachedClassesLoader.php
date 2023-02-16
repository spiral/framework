<?php

declare(strict_types=1);

namespace Spiral\Tokenizer\Listener;

use Spiral\Attributes\ReaderInterface;
use Spiral\Boot\MemoryInterface;
use Spiral\Tokenizer\Attribute\AbstractTarget;
use Spiral\Tokenizer\TokenizationListenerInterface;

final class CachedClassesLoader implements ClassesLoaderInterface
{
    public function __construct(
        private readonly ReaderInterface $reader,
        private readonly MemoryInterface $memory,
        private readonly ClassLocatorByTarget $locator,
        private readonly ListenerInvoker $invoker,
    ) {
    }

    public function loadClasses(TokenizationListenerInterface $listener): bool
    {
        $targets = \iterator_to_array($this->parseAttributes($listener));

        // If there are no definitions, then listener can't be cached.
        if ($targets === []) {
            return false;
        }

        $listenerClasses = [];

        // We decided to load classes for each definition separately.
        // It allows us to cache classes for each definition separately and if we reuse the
        // same target in multiple listeners, we will not have to load classes for the same target.
        foreach ($targets as $target) {
            $cacheKey = (string)$target;

            $classes = $this->memory->loadData($cacheKey);
            if ($classes === null) {
                $this->memory->saveData(
                    $cacheKey,
                    $classes = $this->locator->getClasses($target),
                );
            }

            $listenerClasses = \array_merge($listenerClasses, $classes);
        }

        $this->invoker->invoke(
            $listener,
            \array_map(static fn(string $class) => new \ReflectionClass($class), \array_unique($listenerClasses)),
        );

        return true;
    }

    private function parseAttributes(TokenizationListenerInterface $listener): \Generator
    {
        $listener = new \ReflectionClass($listener);

        foreach ($this->reader->getClassMetadata($listener) as $attribute) {
            if ($attribute instanceof AbstractTarget) {
                yield $attribute;
            }
        }
    }
}
