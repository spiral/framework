<?php

declare(strict_types=1);

namespace Spiral\Filters\Model\Schema;

use Spiral\Filters\Attribute\Setter;
use Spiral\Filters\Model\Factory\FilterFactory;
use Spiral\Filters\Model\FilterProviderInterface;
use Spiral\Filters\Exception\ValidationException;
use Spiral\Filters\InputInterface;

/**
 * Returns data from InputManager based on mapping schema and apply setters.
 */
final class InputMapper
{
    public function __construct(
        private readonly FilterProviderInterface $provider,
        private readonly SchemaProviderInterface $schemaProvider,
        private readonly FilterFactory $filterFactory
    ) {
    }

    public function map(array $mappingSchema, InputInterface $input, array $setters = []): array
    {
        $errors = [];
        $result = $this->mapData($mappingSchema, $input, $setters);

        foreach ($mappingSchema as $field => $map) {
            if (empty($map[Builder::SCHEMA_FILTER])) {
                continue;
            }

            $nested = $map[Builder::SCHEMA_FILTER];
            if (empty($map[Builder::SCHEMA_ARRAY])) {
                // slicing down
                try {
                    $result[$field] = $this->provider->createFilter($nested, $input->withPrefix($map[Builder::SCHEMA_ORIGIN]));
                } catch (ValidationException $e) {
                    $errors[$field] = $e->errors;
                    unset($result[$field]);
                }
                continue;
            }

            $values = [];

            // List of "key" => "location in request"
            foreach ($this->iterate($map, $input) as $index => $origin) {
                try {
                    $values[$index] = $this->provider->createFilter($nested, $input->withPrefix($origin));
                } catch (ValidationException $e) {
                    $errors[$field][$index] = $e->errors;
                }
            }

            $result[$field] = $values;
        }

        return [$result, $errors];
    }

    public function mapData(array $mappingSchema, InputInterface $input, array $setters = []): array
    {
        $result = [];
        foreach ($mappingSchema as $field => $map) {
            if (empty($map[Builder::SCHEMA_FILTER])) {
                $value = $input->getValue($map[Builder::SCHEMA_SOURCE], $map[Builder::SCHEMA_ORIGIN]);

                if ($value !== null) {
                    /** @var Setter $setter */
                    foreach ($setters[$field] ?? [] as $setter) {
                        $value = $setter->updateValue($value);
                    }

                    $result[$field] = $value;
                }
                continue;
            }

            if (empty($map[Builder::SCHEMA_ARRAY])) {
                $filter = $this->filterFactory->createFilterInstance($map[Builder::SCHEMA_FILTER]);
                $result[$field] = $this->mapData(
                    $this->schemaProvider->getSchema($filter),
                    $input->withPrefix($map[Builder::SCHEMA_ORIGIN]),
                    $this->schemaProvider->getSetters($filter)
                );
            }
        }

        return $result;
    }

    /**
     * Create set of origins and prefixed for a nested array of models.
     */
    private function iterate(array $schema, InputInterface $input): \Generator
    {
        $values = $input->getValue(
            $schema[Builder::SCHEMA_ITERATE_SOURCE],
            $schema[Builder::SCHEMA_ITERATE_ORIGIN]
        );

        if (empty($values) || !\is_array($values)) {
            return [];
        }

        foreach (\array_keys($values) as $key) {
            yield $key => $schema[Builder::SCHEMA_ORIGIN] . '.' . $key;
        }
    }
}
