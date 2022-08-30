<?php

/**
 * Spiral Framework. PHP Data Grid
 *
 * @author Valentin Vintsukevich (vvval)
 */

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Value;

use Spiral\DataGrid\Specification\ValueInterface;

final class NotEmpty implements ValueInterface
{
    /** @var ValueInterface|null */
    private $value;

    public function __construct(?ValueInterface $value = null)
    {
        $this->value = $value;
    }

    /**
     * @inheritDoc
     */
    public function accepts($value): bool
    {
        if (empty($value)) {
            return false;
        }

        if ($this->value instanceof ValueInterface) {
            return $this->value->accepts($value);
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function convert($value)
    {
        if ($this->value instanceof ValueInterface) {
            return $this->value->convert($value);
        }

        return $value;
    }
}
