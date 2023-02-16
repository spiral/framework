<?php

declare(strict_types=1);

namespace Spiral\Tokenizer\Listener;

use Spiral\Boot\MemoryInterface;
use Spiral\Tokenizer\TokenizationListenerInterface;

final class CachedClassesLoader implements ClassesLoaderInterface
{
    public function __construct(
        private readonly AttributesParser $parser,
        private readonly MemoryInterface $memory,
        private readonly ClassLocatorByDefinition $cacheBuilder,
        private readonly ListenerInvoker $invoker,
    ) {
    }

    public function loadClasses(TokenizationListenerInterface $listener): bool
    {
        $definitions = \iterator_to_array($this->parser->parse($listener));

        // If there are no definitions, then listener can't be cached.
        if ($definitions === []) {
            return false;
        }

        $listenerClasses = [];

        // We decided to load classes for each definition separately.
        // It allows us to cache classes for each definition separately and if we reuse the
        // same target in multiple listeners, we will not have to load classes for the same target.
        foreach ($definitions as $definition) {
            $cacheKey = $definition->getCacheKey();

            $classes = $this->memory->loadData($cacheKey);
            if ($classes === null) {
                $this->memory->saveData(
                    $cacheKey,
                    $classes = $this->cacheBuilder->getClasses($definition),
                );
            }

            $listenerClasses = \array_merge($listenerClasses, $classes);
        }

        $this->invoker->invoke(
            $listener,
            \array_map(static fn (string $class) => new \ReflectionClass($class), \array_unique($listenerClasses)),
        );

        return true;
    }
}
