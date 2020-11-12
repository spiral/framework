<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Attributes\Native;

use Spiral\Attributes\ReaderInterface;
use Spiral\Attributes\ResolverInterface;

class NativeResolver implements ResolverInterface
{
    /**
     * {@inheritDoc}
     */
    public function isSupported(): bool
    {
        return \version_compare(\PHP_VERSION, '8.0') >= 0;
    }

    /**
     * {@inheritDoc}
     */
    public function create(): ReaderInterface
    {
        return new NativeReader();
    }
}
