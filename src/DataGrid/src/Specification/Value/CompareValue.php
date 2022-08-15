<?php

/**
 * Spiral Framework. PHP Data Grid
 *
 * @author Valentin Vintsukevich (vvval)
 */

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Value;

use Spiral\DataGrid\Exception\ValueException;
use Spiral\DataGrid\Specification\ValueInterface;

abstract class CompareValue implements ValueInterface
{
    /** @var ValueInterface */
    private $base;

    public function __construct(ValueInterface $base)
    {
        if ($base instanceof ArrayValue) {
            throw new ValueException(sprintf('Scalar value type expected, got `%s`', get_class($base)));
        }

        $this->base = $base instanceof self ? $base->base : $base;
    }

    /**
     * @inheritDoc
     */
    public function accepts($value): bool
    {
        if (!$this->base->accepts($value)) {
            return false;
        }

        return $this->compare($this->convert($value));
    }

    /**
     * @inheritDoc
     */
    public function convert($value)
    {
        return $this->base->convert($value);
    }

    /**
     * Checks if value comparison with zero is ok.
     *
     * @param mixed $value
     */
    abstract protected function compare($value): bool;
}
