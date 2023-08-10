<?php

declare(strict_types=1);

namespace Spiral\Filters\Model\Schema;

use Spiral\Attributes\ReaderInterface as AttributesReader;
use Spiral\Filters\Attribute\Input\AbstractInput;
use Spiral\Filters\Attribute\NestedArray;
use Spiral\Filters\Attribute\NestedFilter;
use Spiral\Filters\Attribute\Setter;
use Spiral\Filters\Model\FilterInterface;

/**
 * @internal
 * Read filter based on attributes and return schema and setters.
 */
final class AttributeReader implements ReaderInterface
{
    public function __construct(
        private readonly AttributesReader $reader
    ) {
    }

    /**
     * Read filter based on attributes and return schema and setters.
     *
     * @return array{0: array, 1: array}
     */
    public function read(FilterInterface $filter): array
    {
        $schema = [];
        $setters = [];
        $class = new \ReflectionClass($filter);

        foreach ($class->getProperties() as $property) {
            /** @var object $attribute */
            foreach ($this->reader->getPropertyMetadata($property) as $attribute) {
                if ($attribute instanceof AbstractInput || $attribute instanceof NestedFilter) {
                    $schema[$property->getName()] = $attribute->getSchema($property);
                } elseif ($attribute instanceof NestedArray) {
                    $prefix = $attribute->input->key ?? $attribute->prefix ?? $property->getName();
                    $schema[$property->getName()] = [$attribute->class, $prefix . '.*'];
                } elseif ($attribute instanceof Setter) {
                    $setters[$property->getName()][] = $attribute;
                }
            }
        }

        return [$schema, $setters];
    }
}
