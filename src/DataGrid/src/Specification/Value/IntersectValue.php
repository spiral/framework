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

final class IntersectValue implements ValueInterface
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

        foreach ($value as $v) {
            if ($this->enum->accepts($v)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function convert($value): array
    {
        $result = [];
        foreach ((array)$value as $v) {
            if ($this->enum->accepts($v)) {
                $result[] = $this->enum->convert($v);
            }
        }

        return $result;
    }
}
