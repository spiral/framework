<?php

declare(strict_types=1);

namespace Spiral\Tokenizer;

/**
 * Listeners are used to process classes found by Tokenizer and to perform some actions with them.
 * For example, you can use listeners to look for classes with specific attributes or classes that
 * implement specific interfaces and register them in your application during application bootstrap.
 * Listeners allow application to be more flexible and increase performance.
 * With listeners, you can also use TargetClass and TargetAttribute attributes to increase performance
 * by filtering classes that are not needed for the listener and caching the result.
 */
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
