<?php

declare(strict_types=1);

namespace Spiral\Filters\Model;

use Psr\Container\ContainerInterface;
use Spiral\Core\CoreInterface;
use Spiral\Core\ResolverInterface;
use Spiral\Filters\Model\Schema\AttributeMapper;
use Spiral\Filters\Model\Schema\Builder;
use Spiral\Filters\Model\Schema\InputMapper;
use Spiral\Filters\InputInterface;
use Spiral\Interceptors\Context\CallContext;
use Spiral\Interceptors\Context\Target;
use Spiral\Interceptors\HandlerInterface;
use Spiral\Models\SchematicEntity;

/**
 * Create filters based on their attributes.
 * @internal
 */
final class FilterProvider implements FilterProviderInterface
{
    private readonly bool $isLegacy;
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly ResolverInterface $resolver,
        private readonly HandlerInterface|CoreInterface $core
    ) {
        $this->isLegacy = !$core instanceof HandlerInterface;
    }

    public function createFilter(string $name, InputInterface $input): FilterInterface
    {
        $attributeMapper = $this->container->get(AttributeMapper::class);
        \assert($attributeMapper instanceof AttributeMapper);

        $filter = $this->createFilterInstance($name);
        [$mappingSchema, $errors, $setters, $optionalFilters] = $attributeMapper->map($filter, $input);

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
        foreach ($optionalFilters as $optionalFilter) {
            $schema[$optionalFilter][Builder::SCHEMA_OPTIONAL] = true;
        }

        [$data, $inputErrors] = $inputMapper->map($schema, $input, $setters);
        $errors = \array_merge($errors, $inputErrors);

        $entity = new SchematicEntity($data, $schema);
        $args = [
            'filterBag' => new FilterBag($filter, $entity, $schema, $errors),
        ];
        return $this->isLegacy
            ? $this->core->callAction($name, 'handle', $args)
            : $this->core->handle(new CallContext(Target::fromPair($name, 'handle'), $args));
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
