<?php

declare(strict_types=1);

namespace Spiral\Filters\Model\Schema;

use Spiral\Filters\Attribute\Setter;
use Spiral\Filters\Exception\NestedInputLimitException;
use Spiral\Filters\Exception\SetterException;
use Spiral\Filters\Exception\ValidationException;
use Spiral\Filters\InputInterface;
use Spiral\Filters\Model\FilterInterface;
use Spiral\Filters\Model\FilterProviderInterface;
use Spiral\Filters\Model\NestedInputLimits;

final class InputMapper
{
    public function __construct(
        private readonly FilterProviderInterface $provider,
        private readonly NestedInputLimits $limits = new NestedInputLimits(),
    ) {}

    /**
     * @param array<class-string<FilterInterface>, array<array-key, FilterInterface|ValidationException>> $built
     *        Results of the nested filter builds already made from the same input, by filter class and
     *        input prefix. They are used as is, and only the rest is built.
     */
    public function map(array $mappingSchema, InputInterface $input, array $setters = [], array $built = []): array
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
                    $result[$field] = $this->createFilter($nested, $input, $map[Builder::SCHEMA_ORIGIN], $built);
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
            foreach ($this->iterate($map, $input, $field, $built[$nested] ?? []) as $index => $origin) {
                try {
                    $values[$index] = $this->createFilter($nested, $input, $origin, $built);
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
     * @param array<class-string<FilterInterface>, array<array-key, FilterInterface|ValidationException>> $built
     *
     * @throws ValidationException
     */
    private function createFilter(string $class, InputInterface $input, string $prefix, array $built): FilterInterface
    {
        $result = $built[$class][$prefix] ?? null;
        if ($result instanceof ValidationException) {
            throw $result;
        }

        return $result ?? $this->provider->createFilter($class, $input->withPrefix($prefix));
    }

    /**
     * Create set of origins and prefixed for a nested array of models.
     *
     * @param array<array-key, FilterInterface|ValidationException> $built Already built elements by input prefix.
     */
    private function iterate(array $schema, InputInterface $input, string $field, array $built): \Generator
    {
        $values = $input->getValue(
            $schema[Builder::SCHEMA_ITERATE_SOURCE],
            $schema[Builder::SCHEMA_ITERATE_ORIGIN],
        );

        if (empty($values) || !\is_array($values)) {
            return [];
        }

        $origins = [];
        foreach (\array_keys($values) as $key) {
            $origins[$key] = $schema[Builder::SCHEMA_ORIGIN] . '.' . $key;
        }

        // Elements that are already built have been counted, only the rest takes from the limit.
        $this->limits->assertCount(
            $field,
            \count($built === [] ? $origins : \array_diff_key(\array_flip($origins), $built)),
        );

        foreach ($origins as $key => $origin) {
            $this->limits->assertKey($field, $key);

            yield $key => $origin;
        }
    }
}
