<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Models;

use Spiral\Models\Exception\AccessException;

/**
 * Accessors used to mock access to model field, control value setting, serializing and etc.
 *
 * Internal agreement declares accessor constructor as:
 * public function __construct($value, array $context = [])
 */
interface ValueInterface
{
    /**
     * Change value of accessor, no keyword "set" used to keep compatibility with model magic
     * methods. Attention, method declaration MUST contain internal validation and filters, MUST NOT
     * affect mocked data directly.
     *
     * @param mixed $data
     *
     * @throws AccessException
     */
    public function setValue($data);

    /**
     * Convert object data into serialized value (array or string for example).
     *
     * @return mixed
     *
     * @throws AccessException
     */
    public function getValue();
}
