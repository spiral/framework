<?php

declare(strict_types=1);

namespace Spiral\Tokenizer\Listener;

use Spiral\Tokenizer\TokenizationListenerInterface;

interface InterfacesLoaderInterface
{
    /**
     * Load interfaces for a given listener from cache.
     * Return true if interfaces found for a given listener and loaded.
     * If loader returns false, listener will be notified about all interfaces.
     */
    public function loadInterfaces(TokenizationListenerInterface $listener): bool;
}
