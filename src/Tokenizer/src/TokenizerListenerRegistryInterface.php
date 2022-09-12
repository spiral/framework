<?php

declare(strict_types=1);

namespace Spiral\Tokenizer;

interface TokenizerListenerRegistryInterface
{
    public function addListener(TokenizationListenerInterface $listener): void;
}
