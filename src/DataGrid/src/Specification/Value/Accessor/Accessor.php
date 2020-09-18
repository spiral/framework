<?php

/**
 * Spiral Framework. PHP Data Grid
 *
 * @author Valentin Vintsukevich (vvval)
 */

declare(strict_types=1);

namespace Spiral\DataGrid\Specification\Value\Accessor;

use Spiral\DataGrid\Specification\ValueInterface;

/**
 * Note that the nested values/accessors are executed after the parent one.
 */
abstract class Accessor implements ValueInterface
{
    /** @var ValueInterface */
    protected $next;

    /**
     * @inheritDoc
     */
    public function __construct(ValueInterface $next)
    {
        $this->next = $next;
    }

    /**
     * @inheritDoc
     */
    final public function accepts($value): bool
    {
        return $this->acceptsCurrent($value) || $this->next->accepts($value);
    }

    /**
     * @inheritDoc
     */
    final public function convert($value)
    {
        return $this->next->convert($this->convertCurrent($value));
    }

    /**
     * @param mixed $value
     * @return bool
     */
    abstract protected function acceptsCurrent($value): bool;

    /**
     * @param mixed $value
     * @return mixed
     */
    abstract protected function convertCurrent($value);
}
