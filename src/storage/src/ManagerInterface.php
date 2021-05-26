<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Storage;

use Spiral\Storage\Exception\InvalidArgumentException;
use Spiral\Storage\Manager\ReadableInterface;

/**
 * @template-implements \IteratorAggregate<string, StorageInterface>
 */
interface ManagerInterface extends
    ReadableInterface,
    \IteratorAggregate,
    \Countable
{
    /**
     * @param string|null $name
     * @return StorageInterface
     * @throws InvalidArgumentException
     */
    public function storage(string $name = null): StorageInterface;

    /**
     * @param string $name
     * @return $this
     */
    public function withDefault(string $name): self;
}
