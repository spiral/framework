<?php

declare(strict_types=1);

namespace Spiral\Tokenizer\Listener;

use Spiral\Tokenizer\TokenizationListenerInterface;

interface ClassesLoaderInterface
{
    /**
     * Load classes for a given listener from cache.
     * Return true if classes found for a given listener and loaded.
     * If loader returns false, listener will be notified about all classes.
     */
    public function loadClasses(TokenizationListenerInterface $listener): bool;
}
