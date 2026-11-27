<?php

declare(strict_types=1);

namespace Spiral\Filters\Model\Schema;

use Spiral\Attributes\ReaderInterface;
use Spiral\Filters\Attribute\CastingErrorMessage;
use Spiral\Filters\Attribute\Input\AbstractInput;
use Spiral\Filters\Attribute\NestedArray;
use Spiral\Filters\Attribute\NestedFilter;
use Spiral\Filters\Attribute\Setter;
use Spiral\Filters\Exception\NestedInputLimitException;
use Spiral\Filters\Exception\SetterException;
use Spiral\Filters\Exception\ValidationException;
use Spiral\Filters\InputInterface;
use Spiral\Filters\Model\FilterInterface;
use Spiral\Filters\Model\FilterProviderInterface;
use Spiral\Filters\Model\Mapper\Mapper;
use Spiral\Filters\Model\NestedInputLimits;

/**
 * @internal
 */
final class AttributeMapper
{
    public function __construct(
        private readonly FilterProviderInterface $provider,
        private readonly ReaderInterface $reader,
        private readonly Mapper $mapper,
        private readonly NestedInputLimits $limits = new NestedInputLimits(),
    ) {}

    /**
     * The last element holds what every nested filter build ended with, by filter class and input
     * prefix, so that {@see InputMapper} does not build the same filters again.
     *
     * @return array{
     *     0: array,
     *     1: array,
     *     2: array,
     *     3: array,
     *     4: array<class-string<FilterInterface>, array<array-key, FilterInterface|ValidationException>>
     * }
     */
    public function map(FilterInterface $filter, InputInterface $input): array
    {
        $errors = [];
        $schema = [];
        $setters = [];
        $optionalFilters = [];
        $built = [];
        $class = new \ReflectionClass($filter);

        foreach ($class->getProperties() as $property) {
            /** @var object $attribute */
            foreach ($this->reader->getPropertyMetadata($property) as $attribute) {
                if ($attribute instanceof AbstractInput) {
                    $value = $attribute->getValue($input, $property);
                    try {
                        $this->setValue($filter, $property, $value);
                    } catch (SetterException $e) {
                        $errors[$property->getName()] = $this->createErrorMessage($e, $property, $value);
                    }
                    $schema[$property->getName()] = $attribute->getSchema($property);
                } elseif ($attribute instanceof NestedFilter) {
                    $prefix = $attribute->prefix ?? $property->name;
                    try {
                        $value = $this->provider->createFilter(
                            $attribute->class,
                            $input->withPrefix($prefix),
                        );
                        $built[$attribute->class][$prefix] = $value;

                        try {
                            $this->setValue($filter, $property, $value);
                        } catch (SetterException $e) {
                            $errors[$property->getName()] = $this->createErrorMessage($e, $property, $value);
                        }
                    } catch (NestedInputLimitException $e) {
                        throw $e->under($prefix);
                    } catch (ValidationException $e) {
                        $built[$attribute->class][$prefix] = $e;
                        if ($this->allowsNull($property)) {
                            $this->setValue($filter, $property, null);
                            $optionalFilters[] = $property->getName();
                        } else {
                            $errors[$prefix] = $e->errors;
                        }
                    }

                    $schema[$property->getName()] = $attribute->getSchema($property);
                } elseif ($attribute instanceof NestedArray) {
                    $values = $attribute->getValue($input, $property);
                    $propertyValues = [];

                    $prefix = $attribute->input->key ?? $attribute->prefix ?? $property->getName();

                    if (\is_array($values)) {
                        $this->limits->assertCount($property->getName(), \count($values));

                        foreach (\array_keys($values) as $key) {
                            $this->limits->assertKey($property->getName(), $key);
                            $origin = $prefix . '.' . $key;

                            try {
                                $propertyValues[$key] = $this->provider->createFilter(
                                    $attribute->class,
                                    $input->withPrefix($origin),
                                );
                                $built[$attribute->class][$origin] = $propertyValues[$key];
                            } catch (NestedInputLimitException $e) {
                                throw $e->under($property->getName(), $key);
                            } catch (ValidationException $e) {
                                $built[$attribute->class][$origin] = $e;
                                /** @psalm-suppress InvalidArrayOffset */
                                $errors[$property->getName()][$key] = $e->errors;
                            }
                        }
                    }

                    $this->setValue($filter, $property, $propertyValues);
                    $schema[$property->getName()] = [$attribute->class, $prefix . '.*'];
                } elseif ($attribute instanceof Setter) {
                    $setters[$property->getName()][] = $attribute;
                }
            }
        }

        return [$schema, $errors, $setters, $optionalFilters, $built];
    }

    private function setValue(FilterInterface $filter, \ReflectionProperty $property, mixed $value): void
    {
        if ($value === null) {
            return;
        }

        $setters = $this->reader->getPropertyMetadata($property, Setter::class);

        foreach ($setters as $setter) {
            $value = $setter->updateValue($value);
        }

        $this->mapper->setValue($filter, $property, $value);
    }

    private function allowsNull(\ReflectionProperty $property): bool
    {
        $type = $property->getType();

        return $type === null || $type->allowsNull();
    }

    private function createErrorMessage(
        SetterException $exception,
        \ReflectionProperty $property,
        mixed $value = null,
    ): string {
        $attribute = $this->reader->firstPropertyMetadata($property, CastingErrorMessage::class);

        if ($attribute === null) {
            return $exception->getMessage();
        }

        return $attribute->getMessage($exception, $value) ?? $exception->getMessage();
    }
}
