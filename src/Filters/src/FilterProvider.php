<?php

declare(strict_types=1);

namespace Spiral\Filters;

use Spiral\Core\Container;
use Spiral\Filters\Exception\SchemaException;

final class FilterProvider implements FilterProviderInterface
{
    public function __construct(
        private readonly Container $container = new Container()
    ) {
    }

    public function createFilter(string $name, InputInterface $input): FilterInterface
    {
        $filter = $this->container->get($name);

        if (!$filter instanceof Filter) {
            throw new SchemaException('Invalid filter class, must be subclass of Filter');
        }

        $filter->__destruct();

        $builder = new Schema\Builder();
        $schema = $builder->makeSchema($name, $filter->mappingSchema());
        $filter->withErrorMapper(new ErrorMapper($schema));

        $inputMapper = $this->container->get(Schema\InputMapper::class);

        return $filter->setValue(
            $inputMapper->map($schema, $input)
        );
    }
}
