<?php

declare(strict_types=1);

namespace Spiral\DataGrid\Input;

use Spiral\DataGrid\InputInterface;

use function Spiral\DataGrid\getValue;
use function Spiral\DataGrid\hasKey;

final class ArrayInput implements InputInterface
{
    public function __construct(
        private array $data
    ) {
    }

    public function withNamespace(string $namespace): InputInterface
    {
        $input = clone $this;

        $namespace = \trim($namespace);
        if ($namespace === '') {
            return $input;
        }

        $input->data = [];

        $data = $this->getValue($namespace, []);
        if (\is_array($data)) {
            $input->data = $data;
        }

        return $input;
    }

    public function getValue(string $option, mixed $default = null): mixed
    {
        if (!$this->hasValue($option)) {
            return $default;
        }

        return getValue($this->data, $option);
    }

    public function hasValue(string $option): bool
    {
        return hasKey($this->data, $option);
    }
}
