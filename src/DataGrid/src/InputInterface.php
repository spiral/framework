<?php

/**
 * Spiral Framework. PHP Data Grid
 *
 * @license MIT
 * @author  Anton Tsitou (Wolfy-J)
 * @author  Valentin Vintsukevich (vvval)
 */

declare(strict_types=1);

namespace Spiral\DataGrid;

interface InputInterface
{
    /**
     * Isolate the input into given namespace (prefix).
     */
    public function withNamespace(string $namespace): InputInterface;

    public function hasValue(string $option): bool;

    /**
     * @param mixed|null $default
     * @return mixed
     */
    public function getValue(string $option, $default = null);
}
