<?php

declare(strict_types=1);

namespace Spiral\Tokenizer\Listener;

use Spiral\Attributes\ReaderInterface;
use Spiral\Boot\MemoryInterface;
use Spiral\Tokenizer\TokenizationListenerInterface;

final class CachedClassesLoader extends AbstractCachedLoader implements ClassesLoaderInterface
{
    public function __construct(
        ReaderInterface $reader,
        MemoryInterface $memory,
        private readonly ClassLocatorByTarget $locator,
        ListenerInvoker $invoker,
        bool $readCache = true,
    ) {
        parent::__construct($reader, $memory, $invoker, $readCache);
    }

    public function loadClasses(TokenizationListenerInterface $listener): bool
    {
        return $this->doLoad(
            $listener,
            $this->locator->getClasses(...),
            static fn (string $class) => new \ReflectionClass($class),
        );
    }
}
