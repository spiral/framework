<?php

/**
 * Spiral Framework. PHP Data Grid
 *
 * @license MIT
 * @author  Anton Tsitou (Wolfy-J)
 * @author  Valentin Vintsukevich (vvval)
 */

declare(strict_types=1);

namespace Spiral\DataGrid\Input;

use Spiral\DataGrid\InputInterface;

final class NullInput implements InputInterface
{
    /**
     * @inheritDoc
     */
    public function withNamespace(string $namespace): InputInterface
    {
        return clone $this;
    }

    /**
     * @inheritDoc
     */
    public function hasValue(string $option): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getValue(string $option, $default = null)
    {
        return $default;
    }
}
