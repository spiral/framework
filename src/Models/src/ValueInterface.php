<?php

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
     * @throws AccessException
     */
    public function setValue(mixed $data): self;

    /**
     * Convert object data into serialized value (array or string for example).
     *
     * @throws AccessException
     */
    public function getValue(): mixed;
}
