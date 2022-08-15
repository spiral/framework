<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Filters;

use Spiral\Filters\Exception\SchemaException;
use Spiral\Models\ModelSchema;
use Spiral\Models\Reflection\ReflectionEntity;

final class SchemaBuilder
{
    // Used to define multiple nested models.
    protected const NESTED   = 0;
    protected const ORIGIN   = 1;
    protected const ITERATE  = 2;
    protected const OPTIONAL = 'optional';

    /** @var ReflectionEntity */
    private $entity;

    public function __construct(ReflectionEntity $entity)
    {
        $this->entity = $entity;
    }

    public function getName(): string
    {
        return $this->entity->getName();
    }

    /**
     * Generate entity schema based on schema definitions.
     *
     *
     * @throws SchemaException
     */
    public function makeSchema(): array
    {
        return [
            // mapping and validation schema
            FilterProvider::MAPPING   => $this->buildMap($this->entity),
            FilterProvider::VALIDATES => $this->entity->getProperty('validates', true) ?? [],

            // entity schema
            ModelSchema::SECURED      => $this->entity->getSecured(),
            ModelSchema::FILLABLE     => $this->entity->getFillable(),
            ModelSchema::MUTATORS     => $this->entity->getMutators(),
        ];
    }

    protected function buildMap(ReflectionEntity $filter): array
    {
        $schema = $filter->getProperty('schema', true);
        if (empty($schema)) {
            throw new SchemaException("Filter `{$filter->getName()}` does not define any schema");
        }

        $result = [];
        foreach ($schema as $field => $definition) {
            $optional = false;

            // short definition
            if (is_string($definition)) {
                // simple scalar field definition
                if (!class_exists($definition)) {
                    [$source, $origin] = $this->parseDefinition($field, $definition);
                    $result[$field] = [
                        FilterProvider::SOURCE => $source,
                        FilterProvider::ORIGIN => $origin,
                    ];
                    continue;
                }

                // singular nested model
                $result[$field] = [
                    FilterProvider::SOURCE   => null,
                    FilterProvider::ORIGIN   => $field,
                    FilterProvider::FILTER   => $definition,
                    FilterProvider::ARRAY    => false,
                    FilterProvider::OPTIONAL => $optional,
                ];

                continue;
            }

            if (!is_array($definition) || count($definition) === 0) {
                throw new SchemaException(
                    "Invalid schema definition at `{$filter->getName()}`->`{$field}`"
                );
            }

            // complex definition
            if (!empty($definition[self::ORIGIN])) {
                $origin = $definition[self::ORIGIN];

                // [class, 'data:something.*'] vs [class, 'data:something']
                $iterate = strpos($origin, '.*') !== false || !empty($definition[self::ITERATE]);
                $origin = rtrim($origin, '.*');
            } else {
                $origin = $field;
                $iterate = true;
            }

            if (!empty($definition[self::OPTIONAL]) && $definition[self::OPTIONAL]) {
                $optional = true;
            }

            // array of models (default isolation prefix)
            $map = [
                FilterProvider::FILTER   => $definition[self::NESTED],
                FilterProvider::SOURCE   => null,
                FilterProvider::ORIGIN   => $origin,
                FilterProvider::ARRAY    => $iterate,
                FilterProvider::OPTIONAL => $optional,
            ];

            if ($iterate) {
                [$source, $origin] = $this->parseDefinition($field, $definition[self::ITERATE] ?? $origin);

                $map[FilterProvider::ITERATE_SOURCE] = $source;
                $map[FilterProvider::ITERATE_ORIGIN] = $origin;
            }

            $result[$field] = $map;
        }

        return $result;
    }

    /**
     * Fetch source name and origin from schema definition. Support forms:
     *
     * field => source        => source:field
     * field => source:origin => source:origin
     *
     * @param string           $field
     * @param string           $definition
     *
     * @return array [$source, $origin]
     */
    private function parseDefinition(string $field, string $definition): array
    {
        if (strpos($definition, ':') === false) {
            return ['data', $definition ?? $field];
        }

        return explode(':', $definition);
    }
}
