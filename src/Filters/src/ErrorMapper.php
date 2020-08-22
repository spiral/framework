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

/**
 * Map errors based on their original location.
 */
final class ErrorMapper
{
    /** @var array */
    private $schema;

    /**
     * @param array $schema
     */
    public function __construct(array $schema)
    {
        $this->schema = $schema;
    }

    /**
     * @param array $errors
     * @return array
     */
    public function mapErrors(array $errors): array
    {
        //De-mapping
        $mapped = [];
        foreach ($errors as $field => $message) {
            if (!isset($this->schema[$field])) {
                $mapped[$field] = $message;
                continue;
            }

            $this->mount($mapped, $this->schema[$field][FilterProvider::ORIGIN], $message);
        }

        return $mapped;
    }

    /**
     * Set element using dot notation.
     *
     * @param array  $array
     * @param string $path
     * @param mixed  $message
     *
     * @throws SchemaException
     */
    private function mount(array &$array, string $path, $message): void
    {
        if ($path === '.') {
            throw new SchemaException(
                "Unable to mount error `{$message}` to `{$path}` (root path is forbidden)"
            );
        }

        $step = explode('.', $path);
        while ($name = array_shift($step)) {
            $array = &$array[$name];
        }

        $array = $message;
    }
}
