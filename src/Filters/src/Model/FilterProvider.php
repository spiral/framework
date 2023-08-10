<?php

declare(strict_types=1);

namespace Spiral\Filters\Model;

use Psr\Container\ContainerInterface;
use Spiral\Core\CoreInterface;
use Spiral\Filters\Model\Factory\FilterFactory;
use Spiral\Filters\Model\Mapper\Mapper;
use Spiral\Filters\Model\Schema\InputMapper;
use Spiral\Filters\InputInterface;
use Spiral\Filters\Model\Schema\SchemaProviderInterface;
use Spiral\Models\SchematicEntity;

/**
 * Create filters based on their attributes.
 * @internal
 */
final class FilterProvider implements FilterProviderInterface
{
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly CoreInterface $core,
        private readonly CoreInterface $validationCore,
        private readonly FilterFactory $filterFactory,
        private readonly SchemaProviderInterface $schemaProvider
    ) {
    }

    public function createFilter(string $name, InputInterface $input): FilterInterface
    {
        $filter = $this->filterFactory->createFilterInstance($name);
        $schema = $this->schemaProvider->getSchema($filter);

        $inputMapper = $this->container->get(InputMapper::class);
        \assert($inputMapper instanceof InputMapper);

        // data with applied setters
        $rawData = $inputMapper->mapData($schema, $input, $this->schemaProvider->getSetters($filter));

        // data with nested filters
        [$data, $errors] = $inputMapper->map($schema, $input, $this->schemaProvider->getSetters($filter));

        // validation
        $errors = $this->validationCore->callAction(
            $name,
            'validation',
            ['filter' => $filter, 'data' => $rawData, 'schema' => $schema, 'errors' => $errors]
        );

        // map data to the filter properties
        $mapper = $this->container->get(Mapper::class);
        \assert($mapper instanceof Mapper);
        $mapper->map($filter, $data);

        $entity = new SchematicEntity($data, $schema);
        return $this->core->callAction($name, 'handle', [
            'filterBag' => new FilterBag($filter, $entity, $schema, $errors),
        ]);
    }
}
