<?php

declare(strict_types=1);

namespace Spiral\Filters\Dto\Schema;

use Spiral\Filters\Dto\FilterProviderInterface;
use Spiral\Filters\Exception\ValidationException;
use Spiral\Filters\InputInterface;

final class InputMapper
{
    public function __construct(
        private readonly FilterProviderInterface $provider
    ) {
    }

    public function map(array $mappingSchema, InputInterface $input): array
    {
        $errors = [];
        $result = [];

        foreach ($mappingSchema as $field => $map) {
            if (empty($map[Builder::SCHEMA_FILTER])) {
                $value = $input->getValue($map[Builder::SCHEMA_SOURCE], $map[Builder::SCHEMA_ORIGIN]);

                if ($value !== null) {
                    $result[$field] = $value;
                }
                continue;
            }

            $nested = $map[Builder::SCHEMA_FILTER];
            if (empty($map[Builder::SCHEMA_ARRAY])) {
                // slicing down
                try {
                    $result[$field] = $this->provider->createFilter($nested, $input->withPrefix($map[Builder::SCHEMA_ORIGIN]));
                } catch (ValidationException $e) {
                    $errors[$field] = $e->errors;
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