<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Filter;

use Spiral\Filters\Exception\InputException;
use Spiral\Filters\InputInterface;
use Spiral\Http\Request\InputManager;

/**
 * Provides ability to use http request scope as filters input.
 */
final class InputScope implements InputInterface
{
    /** @var InputManager */
    private $input;

    /**
     * @param InputManager $input
     */
    public function __construct(InputManager $input)
    {
        $this->input = $input;
    }

    /**
     * @inheritdoc
     */
    public function withPrefix(string $prefix, bool $add = true): InputInterface
    {
        $input = clone $this;
        $input->input = $this->input->withPrefix($prefix, $add);

        return $input;
    }

    /**
     * @inheritdoc
     */
    public function getValue(string $source, string $name = null)
    {
        if (!method_exists($this->input, $source)) {
            throw new InputException("Undefined input source '{$source}'");
        }

        return call_user_func([$this->input, $source], $name);
    }
}