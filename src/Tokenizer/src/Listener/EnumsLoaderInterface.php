<?php

declare(strict_types=1);

namespace Spiral\Tokenizer\Listener;

use Spiral\Tokenizer\TokenizationListenerInterface;

interface EnumsLoaderInterface
{
    /**
     * Load enums for a given listener from cache.
     * Return true if enums found for a given listener and loaded.
     * If loader returns false, listener will be notified about all enums.
     */
    public function loadEnums(TokenizationListenerInterface $listener): bool;
}
