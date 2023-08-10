<?php

declare(strict_types=1);

namespace Spiral\Filters\Model\Factory;

use Spiral\Core\ResolverInterface;
use Spiral\Filters\Model\FilterInterface;

/**
 * @internal
 */
final class FilterFactory
{
    public function __construct(
        private readonly ResolverInterface $resolver,
    ) {
    }

    public function createFilterInstance(string $name): FilterInterface
    {
        $class = new \ReflectionClass($name);

        $args = [];
        if ($constructor = $class->getConstructor()) {
            $args = $this->resolver->resolveArguments($constructor);
        }

        return $class->newInstanceArgs($args);
    }
}
