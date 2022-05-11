<?php

declare(strict_types=1);

namespace Spiral\Filters;

use Spiral\Core\Container;
use Spiral\Core\CoreInterface;
use Spiral\Filters\Exception\ValidationException;
use Spiral\Models\SchematicEntity;

final class FilterProvider implements FilterProviderInterface
{
    public function __construct(
        private readonly Container $container,
        private readonly CoreInterface $core,
        private readonly Schema\InputMapper $inputMapper,
        private readonly Schema\Builder $schemaBuilder,
        private readonly Schema\AttributeMapper $attributeMapper
    ) {
    }

    public function createFilter(string $name, InputInterface $input): FilterInterface
    {
        $filter = $this->createFilterInstance($name);
        [$mappingSchema, $errors] = $this->attributeMapper->map($filter, $input);

        if ($filter instanceof HasFilterDefinition) {
            $mappingSchema = \array_merge(
                $mappingSchema,
                $filter->filterDefinition()->mappingSchema()
            );
        }

        $schema = $this->schemaBuilder->makeSchema($name, $mappingSchema);

        $data = [];

        try {
            $data = $this->inputMapper->map($schema, $input);
        } catch (ValidationException $e) {
            $errors = \array_merge($errors, $e->errors);
        }

        $entity = new SchematicEntity($data, $schema);
        return $this->core->callAction($name, 'handle', [
            'filterBag' => new FilterBag($filter, $entity, $schema, $errors),
        ]);
    }

    private function createFilterInstance(string $name): FilterInterface
    {
        $class = new \ReflectionClass($name);

        $args = [];
        if ($constructor = $class->getConstructor()) {
            $args = $this->container->resolveArguments($constructor);
        }

        return $class->newInstanceArgs($args);
    }
}
