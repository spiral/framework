<?php

declare(strict_types=1);

namespace Spiral\Filter;

use Spiral\Filters\Exception\InputException;
use Spiral\Http\Exception\InputException as HttpInputException;
use Spiral\Filters\InputInterface;
use Spiral\Http\Request\InputManager;

/**
 * Provides ability to use http request scope as filters input.
 */
final class InputScope implements InputInterface
{
    public function __construct(
        private InputManager $input
    ) {
    }

    public function withPrefix(string $prefix, bool $add = true): InputInterface
    {
        $input = clone $this;
        $input->input = $this->input->withPrefix($prefix, $add);

        return $input;
    }

    public function getValue(string $source, string $name = null): mixed
    {
        if (!\method_exists($this->input, $source)) {
            throw new InputException(\sprintf('Undefined input source %s', $source));
        }

        return \call_user_func([$this->input, $source], $name);
    }

    public function hasValue(string $source, string $name): bool
    {
        if (!method_exists($this->input, $source)) {
            return false;
        }

        return $this->input->bag($source)->has($name);
    }
}
