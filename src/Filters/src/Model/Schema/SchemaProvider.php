<?php

declare(strict_types=1);

namespace Spiral\Filters\Model\Schema;

use Spiral\Filters\Model\FilterInterface;

/**
 * @internal
 * Read filter and return merged schema and setters from all sources.
 */
final class SchemaProvider implements SchemaProviderInterface
{
    /**
     * @var array<class-string, array>
     */
    private array $setters = [];

    /**
     * @var array<class-string, array>
     */
    private array $schema = [];

    /**
     * @param array<ReaderInterface> $readers
     */
    public function __construct(
        private readonly array $readers,
        private readonly Builder $schemaBuilder
    ) {
    }

    public function getSchema(FilterInterface $filter): array
    {
        if (!isset($this->schema[$filter::class])) {
            $this->read($filter);
        }

        return $this->schemaBuilder->makeSchema($filter::class, $this->schema[$filter::class]);
    }

    public function getSetters(FilterInterface $filter): array
    {
        if (!isset($this->setters[$filter::class])) {
            $this->read($filter);
        }

        return $this->setters[$filter::class];
    }

    private function read(FilterInterface $filter): void
    {
        $this->schema[$filter::class] = [];
        $this->setters[$filter::class] = [];
        foreach ($this->readers as $reader) {
            [$readSchema, $readSetters] = $reader->read($filter);
            $this->schema[$filter::class] = \array_merge($this->schema[$filter::class], $readSchema);
            $this->setters[$filter::class] = \array_merge($this->setters[$filter::class], $readSetters);
        }
    }
}
