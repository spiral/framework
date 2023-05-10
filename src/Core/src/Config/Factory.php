<?php

declare(strict_types=1);

namespace Spiral\Core\Config;

/**
 * Make a value using a closure.
 */
final class Factory extends Binding
{
    private int $parametersCount;

    public function __construct(
        public readonly \Closure $factory,
        public readonly bool $singleton = false,
    ) {
        $this->parametersCount = (new \ReflectionFunction($factory))->getNumberOfParameters();
    }

    /**
     * @return bool Returns {@see false} if the {@see self::$factory} doesn't require arguments.
     */
    public function hasParameters(): bool
    {
        return $this->parametersCount > 0;
    }
}
