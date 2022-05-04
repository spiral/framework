<?php

declare(strict_types=1);

namespace Spiral\Filters\Schema;

use Spiral\Attributes\ReaderInterface;
use Spiral\Filters\Attribute\Input\Input;
use Spiral\Filters\Attribute\NestedArray;
use Spiral\Filters\Attribute\NestedFilter;
use Spiral\Filters\Attribute\Setter;
use Spiral\Filters\Exception\ValidationException;
use Spiral\Filters\FilterInterface;
use Spiral\Filters\FilterProviderInterface;
use Spiral\Filters\InputInterface;

final class AttributeMapper
{
    private array $errors = [];

    public function __construct(
        private readonly FilterProviderInterface $provider,
        private readonly ReaderInterface $reader
    ) {
    }

    /**
     * @return array{0: array, 1: array}
     */
    public function map(FilterInterface $filter, InputInterface $input): array
    {
        $schema = [];
        $class = new \ReflectionClass($filter);

        foreach ($class->getProperties() as $property) {
            $property->setAccessible(true);

            foreach ($this->reader->getPropertyMetadata($property) as $attribute) {
                if ($attribute instanceof Input) {
                    $this->setValue(
                        $filter,
                        $property,
                        $attribute->getValue($input, $property)
                    );
                    $schema[$property->getName()] = $attribute->getSchema($property);
                } elseif ($attribute instanceof NestedFilter) {
                    try {
                        $value = $this->provider->createFilter(
                            $attribute->class,
                            $attribute->prefix ? $input->withPrefix($attribute->prefix) : $input
                        );

                        $this->setValue($filter, $property, $value);
                    } catch (ValidationException $e) {
                        if ($attribute->prefix) {
                            $this->errors[$attribute->prefix] = $e->getErrors();
                        } else {
                            $this->errors = \array_merge($this->errors, $e->getErrors());
                        }
                    }

                    $schema[$property->getName()] = $attribute->getSchema($property);
                } elseif ($attribute instanceof NestedArray) {
                    $values = $attribute->getValue($input, $property);
                    $propertyValues = [];

                    $prefix = $attribute->input->key ?? $property->getName();

                    if (\is_array($values)) {
                        foreach (\array_keys($values) as $key) {
                            try {
                                $propertyValues[$key] = $this->provider->createFilter(
                                    $attribute->class,
                                    $input->withPrefix($prefix . '.' . $key)
                                );
                            } catch (ValidationException $e) {
                                $this->errors[$property->getName()][$key] = $e->getErrors();
                            }
                        }
                    }

                    $this->setValue(
                        $filter,
                        $property,
                        $propertyValues
                    );

                    $schema[$property->getName()] = [$attribute->class, $prefix . '.*'];
                }
            }
        }

        return [$schema, $this->errors];
    }

    private function setValue(FilterInterface $filter, \ReflectionProperty $property, mixed $value): void
    {
        /** @var Setter|null $setter */
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
