<?php

declare(strict_types=1);

namespace Spiral\Config\Patch\Traits;

use Spiral\Config\Exception\DotNotFoundException;

trait DotTrait
{
    /**
     * @param array $data Pointer.
     */
    private function &dotGet(array &$data, string $name): mixed
    {
        //Generating path relative to a given name and prefix
        $path = (!empty($this->prefix) ? $this->prefix . '.' : '') . $name;
        if (empty($path)) {
            return $data;
        }

        $path = \explode('.', \rtrim($path, '.'));
        foreach ($path as $step) {
            if (!\is_array($data) || !\array_key_exists($step, $data)) {
                throw new DotNotFoundException(\sprintf("Unable to find config element '%s'.", $name));
            }
            $data = &$data[$step];
        }

        return $data;
    }
}
