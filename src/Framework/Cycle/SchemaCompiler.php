<?php

declare(strict_types=1);

namespace Spiral\Cycle;

use Cycle\Schema\Compiler;
use Cycle\Schema\Registry;
use Spiral\Boot\MemoryInterface;

final class SchemaCompiler
{
    private const MEMORY_SECTION = 'cycle';
    private const EMPTY_SCHEMA   = ':empty:';

    /** @var mixed */
    private $schema;

    private function __construct($schema)
    {
        $this->schema = $schema;
    }

    public static function fromMemory(MemoryInterface $memory): self
    {
        return new self($memory->loadData(self::MEMORY_SECTION));
    }

    public static function compile(Registry $registry, array $generators): self
    {
        return new self((new Compiler())->compile($registry, $generators));
    }

    public function isEmpty(): bool
    {
        return empty($this->schema);
    }

    public function toSchema(): array
    {
        return ($this->schema === self::EMPTY_SCHEMA || !is_array($this->schema)) ? [] : $this->schema;
    }

    public function toMemory(MemoryInterface $memory)
    {
        return $memory->saveData(
            self::MEMORY_SECTION,
            empty($this->schema) ? self::EMPTY_SCHEMA : $this->schema
        );
    }
}
