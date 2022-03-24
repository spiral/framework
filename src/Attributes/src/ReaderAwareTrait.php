<?php

declare(strict_types=1);

namespace Spiral\Attributes;

/**
 * @mixin ReaderAwareInterface
 */
trait ReaderAwareTrait
{
    private ?ReaderInterface $reader = null;

    public function withReader(ReaderInterface $reader): ReaderAwareInterface
    {
        return (clone $this)->setReader($reader);
    }

    public function getReader(): ReaderInterface
    {
        \assert($this->reader !== null, 'Invariant violation');

        return $this->reader;
    }

    /**
     * @return $this|ReaderAwareInterface
     */
    protected function setReader(ReaderInterface $reader): ReaderAwareInterface
    {
        $this->reader = $reader;

        return $this;
    }
}
