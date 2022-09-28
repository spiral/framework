<?php

declare(strict_types=1);

namespace Spiral\Filters;

use Spiral\Filters\Exception\SchemaException;

final class ErrorMapper
{
    public function __construct(
        private readonly array $schema
    ) {
    }

    /**
     * @param array<string, string> $errors
     * @return array<string, string>
     */
    public function mapErrors(array $errors): array
    {
        // De-mapping
        $mapped = [];
        foreach ($errors as $field => $message) {
            if (!isset($this->schema[$field])) {
                $mapped[$field] = $message;
                continue;
            }

            $this->mount($mapped, $this->schema[$field][Model\Schema\Builder::SCHEMA_ORIGIN], $message);
        }

        return $mapped;
    }

    /**
     * Set element using dot notation.
     *
     * @throws SchemaException
     */
    private function mount(array &$array, string $path, mixed $message): void
    {
        if ($path === '.') {
            throw new SchemaException(
                \sprintf('Unable to mount error `%s` to `%s` (root path is forbidden)', $message, $path)
            );
        }

        $step = \explode('.', $path);
        while ($name = \array_shift($step)) {
            $array = &$array[$name];
        }

        $array = $message;
    }
}
