<?php

declare(strict_types=1);

namespace Spiral\Filters\Model\Schema;

use Spiral\Filters\Attribute\Setter;
use Spiral\Filters\Exception\NestedInputLimitException;
use Spiral\Filters\Exception\SetterException;
use Spiral\Filters\Exception\ValidationException;
use Spiral\Filters\InputInterface;
use Spiral\Filters\Model\FilterProviderInterface;
use Spiral\Filters\Model\NestedInputLimits;

final class InputMapper
{
    public function __construct(
        private readonly FilterProviderInterface $provider,
        private readonly NestedInputLimits $limits = new NestedInputLimits(),
    ) {}

    public function map(array $mappingSchema, InputInterface $input, array $setters = []): array
    {
        $errors = [];
        $result = [];

        foreach ($mappingSchema as $field => $map) {
            if (empty($map[Builder::SCHEMA_FILTER])) {
                $value = $input->getValue($map[Builder::SCHEMA_SOURCE], $map[Builder::SCHEMA_ORIGIN]);

                if ($value !== null) {
                    /** @var Setter $setter */
                    foreach ($setters[$field] ?? [] as $setter) {
                        try {
                            $value = $setter->updateValue($value);
                        } catch (SetterException $e) {
                            $errors[$field] = $e->getMessage();
                        }
                    }

                    $result[$field] = $value;
                }
                continue;
            }

            $nested = $map[Builder::SCHEMA_FILTER];
            if (empty($map[Builder::SCHEMA_ARRAY])) {
                // slicing down
                try {
                    $result[$field] = $this->provider->createFilter($nested, $input->withPrefix($map[Builder::SCHEMA_ORIGIN]));
                } catch (NestedInputLimitException $e) {
                    throw $e->under($field);
                } catch (ValidationException $e) {
                    if ($map[Builder::SCHEMA_OPTIONAL]) {
                        $result[$field] = null;
                        continue;
                    }
                    $errors[$field] = $e->errors;
                }
                continue;
            }

            $values = [];

            // List of "key" => "location in request"
            foreach ($this->iterate($map, $input, $field) as $index => $origin) {
                try {
                    $values[$index] = $this->provider->createFilter($nested, $input->withPrefix($origin));
                } catch (NestedInputLimitException $e) {
                    throw $e->under($field, $index);
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
    private function iterate(array $schema, InputInterface $input, string $field): \Generator
    {
        $values = $input->getValue(
            $schema[Builder::SCHEMA_ITERATE_SOURCE],
            $schema[Builder::SCHEMA_ITERATE_ORIGIN],
        );

        if (empty($values) || !\is_array($values)) {
            return [];
        }

        foreach (\array_keys($values) as $key) {
            $this->limits->assertKey($field, $key);

            yield $key => $schema[Builder::SCHEMA_ORIGIN] . '.' . $key;
        }
    }
}
