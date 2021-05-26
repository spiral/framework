<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Storage\File;

use Spiral\Storage\StorageInterface;

interface EntryInterface extends \Stringable
{
    /**
     * @return string
     */
    public function getPathname(): string;

    /**
     * @return StorageInterface
     */
    public function getStorage(): StorageInterface;
}
