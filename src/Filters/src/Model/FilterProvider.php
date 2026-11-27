<?php

declare(strict_types=1);

namespace Spiral\Filters\Model;

use Psr\Container\ContainerInterface;
use Spiral\Core\CoreInterface;
use Spiral\Core\ResolverInterface;
use Spiral\Filters\Exception\NestedInputLimitException;
use Spiral\Filters\InputInterface;
use Spiral\Filters\Model\Schema\AttributeMapper;
use Spiral\Filters\Model\Schema\Builder;
use Spiral\Filters\Model\Schema\InputMapper;
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

    // Resolved on first use: the mappers depend on this provider, so the constructor cannot get them.
    private ?AttributeMapper $attributeMapper = null;
    private ?InputMapper $inputMapper = null;
    private ?Builder $schemaBuilder = null;

    public function __construct(
        private readonly ContainerInterface $container,
        private readonly ResolverInterface $resolver,
        private readonly HandlerInterface|CoreInterface $core,
        private ?NestedInputLimits $limits = null,
    ) {
        $this->isLegacy = !$core instanceof HandlerInterface;
    }

    /**
     * @throws NestedInputLimitException
     */
    public function createFilter(string $name, InputInterface $input): FilterInterface
    {
        // Taken from the container when not given: the mappers get theirs there, and the counter
        // works only when all three share one instance.
        $limits = $this->limits ??= $this->container->get(NestedInputLimits::class);

        $limits->enterFilter();
        try {
            return $this->buildFilter($name, $input);
        } finally {
            $limits->leaveFilter();
        }
    }

    private function buildFilter(string $name, InputInterface $input): FilterInterface
    {
        $this->attributeMapper ??= $this->container->get(AttributeMapper::class);
        $this->inputMapper ??= $this->container->get(InputMapper::class);
        $this->schemaBuilder ??= $this->container->get(Builder::class);

        $filter = $this->createFilterInstance($name);
        [$mappingSchema, $errors, $setters, $optionalFilters, $built] = $this->attributeMapper->map($filter, $input);

        if ($filter instanceof HasFilterDefinition) {
            $mappingSchema = \array_merge(
                $mappingSchema,
                $filter->filterDefinition()->mappingSchema(),
            );
        }

        $schema = $this->schemaBuilder->makeSchema($name, $mappingSchema);
        foreach ($optionalFilters as $optionalFilter) {
            $schema[$optionalFilter][Builder::SCHEMA_OPTIONAL] = true;
        }

        [$data, $inputErrors] = $this->inputMapper->map($schema, $input, $setters, $built);
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
