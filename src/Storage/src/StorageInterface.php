<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Storage;

use Spiral\Storage\Storage\ReadableInterface;
use Spiral\Storage\Storage\UriResolvableInterface;
use Spiral\Storage\Storage\WritableInterface;

interface StorageInterface extends
    ReadableInterface,
    WritableInterface,
    UriResolvableInterface
{
    /**
     * @param string $pathname
     * @return FileInterface
     */
    public function file(string $pathname): FileInterface;
}
