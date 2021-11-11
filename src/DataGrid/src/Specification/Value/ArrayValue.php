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

final class ArrayValue implements ValueInterface
{
    /** @var ValueInterface */
    private $base;

    public function __construct(ValueInterface $base)
    {
        $this->base = $base instanceof self ? $base->base : $base;
    }

    /**
     * @inheritDoc
     */
    public function accepts($value): bool
    {
        if (!is_array($value)) {
            return false;
        }

        foreach ($value as $child) {
            if (!$this->base->accepts($child)) {
                return false;
            }
        }

        return count($value) > 0;
    }

    /**
     * @inheritDoc
     */
    public function convert($value): array
    {
        $result = [];
        foreach ($value as $child) {
            $result[] = $this->base->convert($child);
        }

        return $result;
    }
}
