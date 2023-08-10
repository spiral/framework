<?php

declare(strict_types=1);

namespace Spiral\Filters\Model\Mapper;

use Spiral\Filters\Model\FilterInterface;

/**
 * @internal
 */
final class Mapper
{
    public function __construct(
        private readonly CasterRegistryInterface $registry
    ) {
    }

    /**
     * Set input data to the filter property.
     */
    public function setValue(FilterInterface $filter, \ReflectionProperty $property, mixed $value): void
    {
        $type = $property->getType();
        if ($type instanceof \ReflectionNamedType && !$type->isBuiltin()) {
            foreach ($this->registry->getCasters() as $setter) {
                if ($setter->supports($type)) {
                    $setter->setValue($filter, $property, $value);
                    return;
                }
            }
        }

        $this->registry->getDefault()->setValue($filter, $property, $value);
    }
}
