<?php

declare(strict_types=1);

namespace Spiral\Tokenizer\Listener;

use Spiral\Attributes\ReaderInterface;
use Spiral\Boot\MemoryInterface;
use Spiral\Tokenizer\Attribute\AbstractTarget;
use Spiral\Tokenizer\TokenizationListenerInterface;

abstract class AbstractCachedLoader
{
    protected string $cacheKeyPrefix = '';

    public function __construct(
        protected readonly ReaderInterface $reader,
        protected readonly MemoryInterface $memory,
        protected readonly ListenerInvoker $invoker,
        protected readonly bool $readCache = true,
    ) {
    }

    protected function doLoad(
        TokenizationListenerInterface $listener,
        callable $locator,
        callable $reflectionBuilder,
    ): bool {
        $targets = \iterator_to_array($this->parseAttributes($listener));

        // If there are no targets, then listener can't be cached.
        if ($targets === []) {
            return false;
        }

        $names = [];

        // We decided to load classes/enums/interfaces for each target separately.
        // It allows us to cache classes/enums/interfaces for each target separately and if we reuse the
        // same target in multiple listeners, we will not have to load classes/enums/interfaces for the same target.
        foreach ($targets as $target) {
            $cacheKey = $this->cacheKeyPrefix . $target;

            $classes = $this->readCache ? $this->memory->loadData($cacheKey) : null;
            if ($classes === null) {
                $this->memory->saveData($cacheKey, $classes = call_user_func($locator, $target));
            }

            $names = \array_merge($names, $classes);
        }

        $this->invoker->invoke($listener, \array_map($reflectionBuilder, \array_unique($names)));

        return true;
    }

    private function parseAttributes(TokenizationListenerInterface $listener): \Generator
    {
        $listener = new \ReflectionClass($listener);

        yield from $this->reader->getClassMetadata($listener, AbstractTarget::class);
    }
}
