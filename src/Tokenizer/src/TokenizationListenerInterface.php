<?php

declare(strict_types=1);

namespace Spiral\Tokenizer;

interface TokenizationListenerInterface
{
    /**
     * The method will be fired for each class that was found by Tokenizer.
     */
    public function listen(\ReflectionClass $class): void;

    /**
     * The method will be fired after all classes were processed by listen method.
     */
    public function finalize(): void;
}
