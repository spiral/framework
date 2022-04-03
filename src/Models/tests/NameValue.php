<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Models;

use Spiral\Models\ValueInterface;

class NameValue implements ValueInterface
{
    private $value;

    public function __construct($value)
    {
        $this->setValue((string)$value);
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function setValue(mixed $data): self
    {
        $this->value = strtoupper($data);

        return $this;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function jsonSerialize()
    {
        return $this->value;
    }
}
