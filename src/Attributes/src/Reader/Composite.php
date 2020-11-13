<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Attributes\Reader;

use Spiral\Attributes\ReaderInterface;

abstract class Composite extends Reader
{
    /**
     * @var ReaderInterface[]
     */
    protected $readers;

    /**
     * @param ReaderInterface[] $readers
     */
    public function __construct(iterable $readers)
    {
        $this->readers = $this->iterableToArray($readers);
    }

    /**
     * @param \Traversable|array $result
     * @return array
     */
    protected function iterableToArray(iterable $result): array
    {
        return $result instanceof \Traversable ? \iterator_to_array($result, false) : $result;
    }
}
