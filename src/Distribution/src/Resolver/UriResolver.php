<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Distribution\Resolver;

use Spiral\Distribution\UriResolverInterface;

abstract class UriResolver implements UriResolverInterface
{
    /**
     * @param string $file
     * @param string|null $prefix
     * @return string
     */
    protected function concat(string $file, ?string $prefix): string
    {
        if ($prefix === null) {
            return $file;
        }

        return \trim($prefix, '/') . '/' . \trim($file, '/');
    }
}
