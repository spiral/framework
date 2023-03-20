<?php

declare(strict_types=1);

namespace Spiral\Filters\Model;

use Psr\Container\ContainerInterface;
use Spiral\Core\Container;
use Spiral\Core\CoreInterface;
use Spiral\Core\ResolverInterface;
use Spiral\Filters\Model\Schema\AttributeMapper;
use Spiral\Filters\Model\Schema\Builder;
use Spiral\Filters\Model\Schema\InputMapper;
use Spiral\Filters\InputInterface;
use Spiral\Models\SchematicEntity;

/**
 * Create filters based on their attributes.
 * @internal
 */
final class FilterProvider implements FilterProviderInterface
{
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly ResolverInterface $resolver,
        private readonly CoreInterface $core
    ) {
    }

    public function createFilter(string $name, InputInterface $input): FilterInterface
    {
        $attributeMapper = $this->container->get(AttributeMapper::class);
        \assert($attributeMapper instanceof AttributeMapper);

        $filter = $this->createFilterInstance($name);
        [$mappingSchema, $errors, $setters] = $attributeMapper->map($filter, $input);

        if ($filter instanceof HasFilterDefinition) {
            $mappingSchema = \array_merge(
                $mappingSchema,
                $filter->filterDefinition()->mappingSchema()
            );
        }

        $inputMapper = $this->container->get(InputMapper::class);
        \assert($inputMapper instanceof InputMapper);

        $schemaBuilder = $this->container->get(Builder::class);
        \assert($schemaBuilder instanceof Builder);

        $schema = $schemaBuilder->makeSchema($name, $mappingSchema);

        [$data, $inputErrors] = $inputMapper->map($schema, $input, $setters);
        $errors = \array_merge($errors, $inputErrors);

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
            $args = $this->resolver->resolveArguments($constructor);
        }

        return $class->newInstanceArgs($args);
    }
}
