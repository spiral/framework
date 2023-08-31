<?php

declare(strict_types=1);

namespace Spiral\Filters;

use Spiral\Filters\Exception\InputException;

/**
 * Supplied filters with value set, must support prefix based slicing.
 */
interface InputInterface
{
    /**
     * Create version of input isolated by a given prefix.
     *
     * In a given examples listed method must produce same result:
     *
     * $input->getValue('data', 'array.value')
     * $input->withPrefix('array')->getValue('data', 'value')
     *
     * @param bool   $add When set to false current prefix path will be overwritten.
     */
    public function withPrefix(string $prefix, bool $add = true): InputInterface;

    /**
     * Get input value based on it's source and name.
     *
     * @throws InputException
     */
    public function getValue(string $source, string $name = null): mixed;

    /**
     * Returns true if the parameter name exists in the source, otherwise returns false.
     */
    public function hasValue(string $source, string $name): bool;
}
