<?php

declare(strict_types=1);

namespace Spiral\Tokenizer\Listener;

use Spiral\Attributes\ReaderInterface;
use Spiral\Boot\MemoryInterface;
use Spiral\Tokenizer\TokenizationListenerInterface;

final class CachedEnumsLoader extends AbstractCachedLoader implements EnumsLoaderInterface
{
    protected string $cacheKeyPrefix = 'enums-';

    public function __construct(
        ReaderInterface $reader,
        MemoryInterface $memory,
        private readonly EnumLocatorByTarget $locator,
        ListenerInvoker $invoker,
        bool $readCache = true,
    ) {
        parent::__construct($reader, $memory, $invoker, $readCache);
    }

    public function loadEnums(TokenizationListenerInterface $listener): bool
    {
        return $this->doLoad(
            $listener,
            $this->locator->getEnums(...),
            static fn (string $enum) => new \ReflectionEnum($enum),
        );
    }
}
