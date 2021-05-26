<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Storage;

interface MutableManagerInterface extends ManagerInterface
{
    /**
     * @param string $name
     * @param StorageInterface $storage
     */
    public function add(string $name, StorageInterface $storage): void;
}
