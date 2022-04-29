<?php

declare(strict_types=1);

namespace Spiral\Filters;

use Spiral\Filters\Exception\DotNotFoundException;

/**
 * Slices over array data.
 */
final class ArrayInput implements InputInterface
{
    private string $prefix = '';

    public function __construct(
        private readonly array $data = []
    ) {
    }

    public function withPrefix(string $prefix, bool $add = true): InputInterface
    {
        $input = clone $this;
        if ($add) {
            $input->prefix .= '.' . $prefix;
            $input->prefix = \trim($input->prefix, '.');
        } else {
            $input->prefix = $prefix;
        }
        return $input;
    }

    public function getValue(string $source, string $name = null): mixed
    {
        try {
            return $this->dotGet($name);
        } catch (DotNotFoundException) {
            return null;
        }
    }

    /**
     * @inheritdoc
     */
    public function hasValue(string $source, string $name): bool
    {
        try {
            $this->dotGet($name);
        } catch (DotNotFoundException $e) {
            return false;
        }

        return true;
    }

    /**
     * Get element using dot notation.
     *
     * @throws DotNotFoundException
     */
    private function dotGet(string $name): mixed
    {
        $data = $this->data;

        //Generating path relative to a given name and prefix
        $path = (!empty($this->prefix) ? $this->prefix . '.' : '') . $name;
        if (empty($path)) {
            return $data;
        }

        $path = \explode('.', \rtrim($path, '.'));
        foreach ($path as $step) {
            if (!\is_array($data) || !\array_key_exists($step, $data)) {
                throw new DotNotFoundException(\sprintf("Unable to find requested element '%s'", $name));
            }
            $data = &$data[$step];
        }

        return $data;
    }
}
