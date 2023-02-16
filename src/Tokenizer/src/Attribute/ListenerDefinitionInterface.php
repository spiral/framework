<?php

declare(strict_types=1);

namespace Spiral\Tokenizer\Attribute;

interface ListenerDefinitionInterface
{
    /**
     * Get scope for class locator. If scope is not set, all classes will be listened.
     * @return non-empty-string|null
     */
    public function getScope(): ?string;

    /**
     * Filter given classes and return only those that should be listened.
     * @param \ReflectionClass[] $classes
     * @return iterable<class-string>
     */
    public function filter(array $classes): iterable;

    /**
     * Get hash for cache key.
     * @return non-empty-string
     */
    public function getCacheKey(): string;
}
