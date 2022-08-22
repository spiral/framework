<?php

declare(strict_types=1);

namespace Spiral\Filters\Model\Schema;

use Spiral\Attributes\ReaderInterface;
use Spiral\Filters\Attribute\Input\AbstractInput;
use Spiral\Filters\Attribute\NestedArray;
use Spiral\Filters\Attribute\NestedFilter;
use Spiral\Filters\Attribute\Setter;
use Spiral\Filters\Model\FilterInterface;
use Spiral\Filters\Model\FilterProviderInterface;
use Spiral\Filters\Exception\ValidationException;
use Spiral\Filters\InputInterface;

/**
 * @internal
 */
final class AttributeMapper
{
    public function __construct(
        private readonly FilterProviderInterface $provider,
        private readonly ReaderInterface $reader
    ) {
    }

    /**
     * Map input data into filter properties with attributes
     *
     * @return array{0: array, 1: array}
     */
    public function map(FilterInterface $filter, InputInterface $input): array
    {
        $errors = [];
        $schema = [];
        $class = new \ReflectionClass($filter);

        foreach ($class->getProperties() as $property) {
            foreach ($this->reader->getPropertyMetadata($property) as $attribute) {
                if ($attribute instanceof AbstractInput) {
                    $this->setValue($filter, $property, $attribute->getValue($input, $property));
                    $schema[$property->getName()] = $attribute->getSchema($property);
                } elseif ($attribute instanceof NestedFilter) {
                    try {
                        $value = $this->provider->createFilter(
                            $attribute->class,
                            $attribute->prefix ?
                                $input->withPrefix($attribute->prefix) :
                                $input->withPrefix($property->name)
                        );

                        $this->setValue($filter, $property, $value);
                    } catch (ValidationException $e) {
                        if ($attribute->prefix) {
                            $errors[$attribute->prefix] = $e->errors;
                        } else {
                            $errors = \array_merge($errors, $e->errors);
                        }
                    }

                    $schema[$property->getName()] = $attribute->getSchema($property);
                } elseif ($attribute instanceof NestedArray) {
                    $values = $attribute->getValue($input, $property);
                    $propertyValues = [];

                    $prefix = $attribute->input->key ?? $attribute->prefix ?? $property->getName();

                    if (\is_array($values)) {
                        foreach (\array_keys($values) as $key) {
                            try {
                                $propertyValues[$key] = $this->provider->createFilter(
                                    $attribute->class,
                                    $input->withPrefix($prefix . '.' . $key)
                                );
                            } catch (ValidationException $e) {
                                $errors[$property->getName()][$key] = $e->errors;
                            }
                        }
                    }

                    $this->setValue($filter, $property, $propertyValues);
                    $schema[$property->getName()] = [$attribute->class, $prefix . '.*'];
                }
            }
        }

        return [$schema, $errors];
    }

    private function setValue(FilterInterface $filter, \ReflectionProperty $property, mixed $value): void
    {
        $setter = $this->reader->firstPropertyMetadata($property, Setter::class);

        if ($value === null) {
            return;
        }

        if ($setter) {
            $value = $setter->updateValue($value);
        }

        $property->setValue($filter, $value);
    }
}
