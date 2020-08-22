<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Filters;

use Spiral\Filters\Exception\DotNotFoundException;

/**
 * Slices over array data.
 */
final class ArrayInput implements InputInterface
{
    /** @var array */
    private $data;

    /** @var string */
    private $prefix = '';

    /**
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * @inheritdoc
     */
    public function withPrefix(string $prefix, bool $add = true): InputInterface
    {
        $input = clone $this;
        if ($add) {
            $input->prefix .= '.' . $prefix;
            $input->prefix = trim($input->prefix, '.');
        } else {
            $input->prefix = $prefix;
        }
        return $input;
    }

    /**
     * @inheritdoc
     */
    public function getValue(string $source, string $name = null)
    {
        try {
            return $this->dotGet($name);
        } catch (DotNotFoundException $e) {
            return null;
        }
    }

    /**
     * Get element using dot notation.
     *
     * @param string $name
     * @return mixed|null
     *
     * @throws DotNotFoundException
     */
    private function dotGet(string $name)
    {
        $data = $this->data;

        //Generating path relative to a given name and prefix
        $path = (!empty($this->prefix) ? $this->prefix . '.' : '') . $name;
        if (empty($path)) {
            return $data;
        }

        $path = explode('.', rtrim($path, '.'));
        foreach ($path as $step) {
            if (!is_array($data) || !array_key_exists($step, $data)) {
                throw new DotNotFoundException("Unable to find requested element '{$name}'");
            }
            $data = &$data[$step];
        }

        return $data;
    }
}
