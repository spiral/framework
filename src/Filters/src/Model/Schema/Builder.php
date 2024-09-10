<?php

declare(strict_types=1);

namespace Spiral\Filters\Model\Schema;

use Spiral\Filters\Exception\SchemaException;

/**
 * @internal
 */
final class Builder
{
    // Packed schema definitions
    public const SCHEMA_SOURCE = 'source';
    public const SCHEMA_ORIGIN = 'origin';
    public const SCHEMA_FILTER = 'filter';
    public const SCHEMA_ARRAY = 'array';
    public const SCHEMA_OPTIONAL = 'optional';
    public const SCHEMA_ITERATE_SOURCE = 'iterate_source';
    public const SCHEMA_ITERATE_ORIGIN = 'iterate_origin';
    // Used to define multiple nested models.
    protected const NESTED = 0;
    protected const ORIGIN = 1;
    protected const ITERATE = 2;
    protected const OPTIONAL = 'optional';


    /**
     * Generate entity schema based on schema definitions.
     *
     * @return array<array-key, array{
     *     array?: bool,
     *     filter?: class-string|mixed,
     *     iterate_origin?: mixed,
     *     iterate_source?: mixed,
     *     optional?: bool,
     *     origin: array-key|mixed,
     *     source: mixed|null
     * }>
     * @throws SchemaException
     */
    public function makeSchema(string $name, array $schema): array
    {
        if (empty($schema)) {
            throw new SchemaException(\sprintf('Filter `%s` does not define any schema', $name));
        }

        $result = [];
        foreach ($schema as $field => $definition) {
            $optional = false;

            // short definition
            if (\is_string($definition)) {
                // simple scalar field definition
                if (!\class_exists($definition)) {
                    [$source, $origin] = $this->parseDefinition($field, $definition);
                    $result[$field] = [
                        self::SCHEMA_SOURCE => $source,
                        self::SCHEMA_ORIGIN => $origin,
                    ];
                    continue;
                }

                // singular nested model
                $result[$field] = [
                    self::SCHEMA_SOURCE => null,
                    self::SCHEMA_ORIGIN => $field,
                    self::SCHEMA_FILTER => $definition,
                    self::SCHEMA_ARRAY => false,
                    self::SCHEMA_OPTIONAL => $optional,
                ];

                continue;
            }

            if (!\is_array($definition) || $definition === []) {
                throw new SchemaException(
                    \sprintf('Invalid schema definition at `%s`->`%s`', $name, $field)
                );
            }

            // complex definition
            if (!empty($definition[self::ORIGIN])) {
                $origin = $definition[self::ORIGIN];

                // [class, 'data:something.*'] vs [class, 'data:something']
                $iterate = \str_contains((string)$origin, '.*') || !empty($definition[self::ITERATE]);
                $origin = \rtrim((string) $origin, '.*');
            } else {
                $origin = $field;
                $iterate = true;
            }

            if (!empty($definition[self::OPTIONAL])) {
                $optional = true;
            }

            // array of models (default isolation prefix)
            $map = [
                self::SCHEMA_FILTER => $definition[self::NESTED],
                self::SCHEMA_SOURCE => null,
                self::SCHEMA_ORIGIN => $origin,
                self::SCHEMA_ARRAY => $iterate,
                self::SCHEMA_OPTIONAL => $optional,
            ];

            if ($iterate) {
                [$source, $origin] = $this->parseDefinition($field, $definition[self::ITERATE] ?? $origin);

                $map[self::SCHEMA_ITERATE_SOURCE] = $source;
                $map[self::SCHEMA_ITERATE_ORIGIN] = $origin;
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
     * @return array [$source, $origin]
     */
    private function parseDefinition(string $field, string $definition): array
    {
        if (!\str_contains($definition, ':')) {
            return ['data', empty($definition) ? $field : $definition];
        }

        return \explode(':', $definition);
    }
}
