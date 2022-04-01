<?php

declare(strict_types=1);

namespace Spiral\DataGrid;

use LogicException;

/**
 * Case insensitive search for a key existence in the given array.
 */
function hasKey(array $array, float|int|string $search): bool
{
    foreach ($array as $key => $_) {
        if (equals($key, $search)) {
            return true;
        }
    }

    return false;
}

function hasValue(array $array, mixed $search): bool
{
    foreach ($array as $value) {
        if (equals($value, $search)) {
            return true;
        }
    }

    return false;
}

/**
 * Get value by a key in the given array using case insensitive case.
 *
 * @return mixed
 * @throws LogicException
 */
function getValue(array $array, string $search)
{
    foreach ($array as $key => $value) {
        if (equals($key, $search)) {
            return $value;
        }
    }

    throw new LogicException(\sprintf('`%s` key is not presented in the array.', $search));
}

/**
 * @internal
 */
function equals(mixed $value1, mixed $value2): bool
{
    if (\is_string($value1) && \is_string($value2) && \strcasecmp($value1, $value2) === 0) {
        return true;
    }

    if (\is_numeric($value1) && \is_numeric($value2) && \strcasecmp((string)$value1, (string)$value2) === 0) {
        return true;
    }

    return $value1 === $value2;
}
