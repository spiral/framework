<?php

declare(strict_types=1);

namespace Spiral\Tokenizer;

/**
 * It contains all listeners that will be noticed about found classes by a class locator.
 * @method getListenerClasses(): array
 */
interface TokenizerListenerRegistryInterface
{
    public function addListener(TokenizationListenerInterface $listener): void;
}
