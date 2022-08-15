<?php

/**
 * Spiral Framework. PHP Data Grid
 *
 * @license MIT
 * @author  Anton Tsitou (Wolfy-J)
 * @author  Valentin Vintsukevich (vvval)
 */

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Value;

use Spiral\DataGrid\Specification\ValueInterface;

final class SubsetValue implements ValueInterface
{
    /** @var ValueInterface */
    private $enum;

    /**
     * @param mixed          ...$values
     */
    public function __construct(ValueInterface $enum, ...$values)
    {
        $this->enum = new EnumValue($enum, ...$values);
    }

    /**
     * @inheritDoc
     */
    public function accepts($value): bool
    {
        $value = (array)$value;

        if (count($value) === 1) {
            return $this->enum->accepts(array_values($value)[0]);
        }

        if (empty($value)) {
            return false;
        }

        return $this->arrayType()->accepts($value);
    }

    /**
     * @inheritDoc
     */
    public function convert($value)
    {
        return $this->arrayType()->convert((array)$value);
    }

    private function arrayType(): ArrayValue
    {
        return new ArrayValue($this->enum);
    }
}
