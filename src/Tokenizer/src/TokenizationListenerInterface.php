<?php

declare(strict_types=1);

namespace Spiral\Tokenizer;

interface TokenizationListenerInterface
{
    /**
     * The method will be fired for every found class by a class locator.
     */
    public function listen(\ReflectionClass $class): void;

    /**
     * The method will be fired after a class locator will finish iterating of found classes.
     */
    public function finalize(): void;
}
