<?php

declare(strict_types=1);

namespace Spiral\Attributes;

interface ReaderAwareInterface
{
    public function withReader(ReaderInterface $reader): self;

    public function getReader(): ReaderInterface;
}
