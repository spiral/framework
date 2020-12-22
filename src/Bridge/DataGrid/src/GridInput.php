<?php

/**
 * Spiral Framework. Data Grid Bridge.
 *
 * @license MIT
 * @author  Anton Tsitou (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\DataGrid;

use Spiral\Http\Request\InputManager;

final class GridInput implements InputInterface
{
    /** @var InputManager */
    private $input;

    /**
     * InputScope constructor.
     *
     * @param InputManager $input
     */
    public function __construct(InputManager $input)
    {
        $this->input = $input;
    }

    /**
     * @param string $prefix
     * @return InputInterface
     */
    public function withNamespace(string $prefix): InputInterface
    {
        $input = clone $this;
        $input->input = $input->input->withPrefix($prefix);

        return $input;
    }

    /**
     * @param string $option
     * @return bool
     */
    public function hasValue(string $option): bool
    {
        return $this->input->input($option) !== null;
    }

    /**
     * @param string $option
     * @param mixed  $default
     * @return mixed|null
     */
    public function getValue(string $option, $default = null)
    {
        return $this->input->input($option, $default);
    }
}
