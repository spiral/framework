<?php

/**
 * Spiral Framework. PHP Data Grid
 *
 * @author Valentin Vintsukevich (vvval)
 */

declare(strict_types=1);

namespace Spiral\DataGrid;

use LogicException;

/**
 * Case insensitive search for a key existence in the given array.
 *
 * @param array            $array
 * @param string|int|float $search
 * @return bool
 */
function hasKey(array $array, $search): bool
{
    foreach ($array as $key => $value) {
        if (equals($key, $search)) {
            return true;
        }
    }

    return false;
}

/**
 * @param array $array
 * @param       $search
 * @return bool
 */
function hasValue(array $array, $search): bool
{
    foreach ($array as $key => $value) {
        if (equals($value, $search)) {
            return true;
        }
    }

    return false;
}

/**
 * Get value by a key in the given array using case insensitive case.
 *
 * @param array  $array
 * @param string $search
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

    throw new LogicException("`$search` key is not presented in the array.");
}

/**
 * @param mixed $value1
 * @param mixed $value2
 * @return bool
 * @internal
 */
function equals($value1, $value2): bool
{
    if (is_string($value1) && is_string($value2) && strcasecmp($value1, $value2) === 0) {
        return true;
    }

    if (is_numeric($value1) && is_numeric($value2) && strcasecmp((string)$value1, (string)$value2) === 0) {
        return true;
    }

    return $value1 === $value2;
}
