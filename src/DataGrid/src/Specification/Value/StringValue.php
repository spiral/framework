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

final class StringValue implements ValueInterface
{
    /** @var bool */
    private $allowEmpty;

    public function __construct(bool $allowEmpty = false)
    {
        $this->allowEmpty = $allowEmpty;
    }

    /**
     * @inheritDoc
     */
    public function accepts($value): bool
    {
        return (is_numeric($value) || is_string($value)) && ($this->allowEmpty || $this->convert($value) !== '');
    }

    /**
     * @inheritDoc
     */
    public function convert($value): string
    {
        return (string)$value;
    }
}
