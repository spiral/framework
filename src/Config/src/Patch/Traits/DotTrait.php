<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Config\Patch\Traits;

use Spiral\Config\Exception\DotNotFoundException;

trait DotTrait
{
    /**
     * @param array  $data Pointer.
     * @param string $name
     * @return array|mixed
     */
    private function &dotGet(array &$data, string $name)
    {
        //Generating path relative to a given name and prefix
        $path = (!empty($this->prefix) ? $this->prefix . '.' : '') . $name;
        if (empty($path)) {
            return $data;
        }

        $path = explode('.', rtrim($path, '.'));
        foreach ($path as $step) {
            if (!is_array($data) || !array_key_exists($step, $data)) {
                throw new DotNotFoundException("Unable to find config element '{$name}'.");
            }
            $data = &$data[$step];
        }

        return $data;
    }
}
