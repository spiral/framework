<?php

declare(strict_types=1);

namespace Spiral\Tokenizer\Attribute;

abstract class AbstractTarget implements \Stringable
{
    /**
     * @param non-empty-string|null $scope
     */
    public function __construct(
        public readonly ?string $scope = null,
    ) {
    }

    /**
     * Generates a unique string for this target to be used as cache key.
     * @return non-empty-string
     */
    public function __toString(): string
    {
        return \md5(\print_r($this, return: true));
    }

    /**
     * Filter given classes and return only those that should be listened.
     * @param \ReflectionClass[] $classes
     * @return \Iterator<class-string>
     */
    abstract public function filter(array $classes): \Iterator;

    /**
     * Get scope for class locator. If scope is not set, all classes will be listened.
     * @return non-empty-string|null
     */
    public function getScope(): ?string
    {
        return $this->scope;
    }
}
