<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Storage;

use Spiral\Storage\File\ReadableInterface;
use Spiral\Storage\File\UriResolvableInterface;
use Spiral\Storage\File\WritableInterface;

interface FileInterface extends
    ReadableInterface,
    WritableInterface,
    UriResolvableInterface
{
}
