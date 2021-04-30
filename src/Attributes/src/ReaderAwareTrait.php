<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Attributes;

/**
 * @mixin ReaderAwareInterface
 */
trait ReaderAwareTrait
{
    /**
     * @var ReaderInterface|null
     */
    private $reader;

    /**
     * @param ReaderInterface $reader
     * @return $this|ReaderAwareInterface
     */
    public function withReader(ReaderInterface $reader): ReaderAwareInterface
    {
        return (clone $this)->setReader($reader);
    }

    /**
     * @return ReaderInterface
     */
    public function getReader(): ReaderInterface
    {
        assert($this->reader !== null, 'Invariant violation');

        return $this->reader;
    }

    /**
     * @param ReaderInterface $reader
     * @return $this|ReaderAwareInterface
     */
    protected function setReader(ReaderInterface $reader): ReaderAwareInterface
    {
        $this->reader = $reader;

        return $this;
    }
}
