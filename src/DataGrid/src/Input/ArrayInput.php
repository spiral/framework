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

use function Spiral\DataGrid\getValue;
use function Spiral\DataGrid\hasKey;

final class ArrayInput implements InputInterface
{
    /** @var array */
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @inheritDoc
     */
    public function withNamespace(string $namespace): InputInterface
    {
        $input = clone $this;

        $namespace = trim($namespace);
        if ($namespace === '') {
            return $input;
        }

        $input->data = [];

        $data = $this->getValue($namespace, []);
        if (is_array($data)) {
            $input->data = $data;
        }

        return $input;
    }

    /**
     * @inheritDoc
     */
    public function getValue(string $option, $default = null)
    {
        if (!$this->hasValue($option)) {
            return $default;
        }

        return getValue($this->data, $option);
    }

    /**
     * @inheritDoc
     */
    public function hasValue(string $option): bool
    {
        return hasKey($this->data, $option);
    }
}
