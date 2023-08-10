<?php

declare(strict_types=1);

namespace Spiral\Filters\Model\Mapper;

use Spiral\Filters\Model\FilterInterface;
use Spiral\Filters\Model\Schema\Builder;
use Spiral\Filters\Model\Schema\SchemaProviderInterface;

/**
 * @internal
 */
final class Mapper
{
    public function __construct(
        private readonly SchemaProviderInterface $schemaProvider,
        private readonly SetterRegistryInterface $registry
    ) {
    }

    /**
     * Map input data into filter properties with attributes.
     */
    public function map(FilterInterface $filter, array $data): void
    {
        $class = new \ReflectionClass($filter);

        foreach ($this->schemaProvider->getSchema($filter) as $field => $map) {
            if (!empty($map[Builder::SCHEMA_FILTER]) && $data[$field] === []) {
                continue;
            }

            $property = $class->getProperty($field);
            if (!empty($map[Builder::SCHEMA_FILTER])) {
                $this->registry->getDefault()->setValue($filter, $property, $data[$field]);
                continue;
            }
            $type = $property->getType();
            if (!isset($data[$field]) && $type->allowsNull()) {
                $this->registry->getDefault()->setValue($filter, $property, null);
                continue;
            }

            if (!$type instanceof \ReflectionNamedType || $type->isBuiltin()) {
                $this->registry->getDefault()->setValue($filter, $property, $data[$field]);
                continue;
            }

            foreach ($this->registry->getSetters() as $setter) {
                if ($setter->supports($type)) {
                    $setter->setValue($filter, $property, $data[$field]);
                }
            }
        }
    }
}
