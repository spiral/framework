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

    public function getParametersCount(): int
    {
        return $this->parametersCount;
    }
}
