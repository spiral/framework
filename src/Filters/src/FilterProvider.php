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
    ) {
    }

    public function createFilter(string $name, InputInterface $input): FilterInterface
    {
        $inputMapper = $this->container->get(Schema\InputMapper::class);

        $filter = $this->createFilterInstance($name);

        $attributeMapper = $this->container->get(Schema\AttributeMapper::class);
        $mappingSchema = $attributeMapper->map($filter, $input);

        $errors = $attributeMapper->getErrors();

        if ($filter instanceof HasFilterDefinition) {
            $mappingSchema = \array_merge(
                $mappingSchema,
                $filter->filterDefinition()->mappingSchema()
            );
        }

        $builder = $this->container->get(Schema\Builder::class);
        $schema = $builder->makeSchema($name, $mappingSchema);

        $data = [];

        try {
            $data = $inputMapper->map($schema, $input);
        } catch (ValidationException $e) {
            $errors = \array_merge($errors, $e->getErrors());
        }

        $bag = new FilterBag(
            $filter,
            new SchematicEntity($data, $schema),
            $schema
        );

        return $this->core->callAction($name, 'handle', [
            'filterBag' => $bag,
            'errors' => $errors,
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
