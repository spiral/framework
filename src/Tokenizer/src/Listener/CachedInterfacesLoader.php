<?php

declare(strict_types=1);

namespace Spiral\Tokenizer\Listener;

use Spiral\Attributes\ReaderInterface;
use Spiral\Boot\MemoryInterface;
use Spiral\Tokenizer\TokenizationListenerInterface;

final class CachedInterfacesLoader extends AbstractCachedLoader implements InterfacesLoaderInterface
{
    protected string $cacheKeyPrefix = 'interfaces-';

    public function __construct(
        ReaderInterface $reader,
        MemoryInterface $memory,
        private readonly InterfaceLocatorByTarget $locator,
        ListenerInvoker $invoker,
        bool $readCache = true,
    ) {
        parent::__construct($reader, $memory, $invoker, $readCache);
    }

    public function loadInterfaces(TokenizationListenerInterface $listener): bool
    {
        return $this->doLoad(
            $listener,
            $this->locator->getInterfaces(...),
            static fn (string $class) => new \ReflectionClass($class),
        );
    }
}
