<?php

declare(strict_types=1);

namespace Spiral\Filters;

use Spiral\Core\Container;
use Spiral\Filters\Exception\SchemaException;

final class FilterProvider implements FilterProviderInterface
{
    /** @var <class-string, array{0: Filter, 1: array}> */
    private array $cache = [];


    public function __construct(
        private readonly Container $container = new Container()
    ) {
    }

    public function createFilter(string $name, InputInterface $input): FilterInterface
    {
        if (isset($this->cache[$name])) {
            [$filter, $schema] = $this->cache[$name];

            // Clear old input data
            $filter->__destruct();
        } else {

            $class = new \ReflectionClass($name);
            $args = $this->container->resolveArguments($class->getConstructor());
            $filter = $class->newInstanceArgs($args);

            if (!$filter instanceof Filter) {
                throw new SchemaException('Invalid filter class, must be subclass of Filter');
            }

            $builder = new Schema\Builder();
            $schema = $builder->makeSchema($name, $filter->mappingSchema());
            $filter->withErrorMapper(new ErrorMapper($schema));

            $this->cache[$name] = [$filter, $schema];
        }

        $inputMapper = $this->container->get(Schema\InputMapper::class);

        return $filter->setValue(
            $inputMapper->map($schema, $input)
        );
    }
}
