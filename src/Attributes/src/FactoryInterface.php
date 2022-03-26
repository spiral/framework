<?php

declare(strict_types=1);

namespace Spiral\Attributes;

interface FactoryInterface
{
    public function create(): ReaderInterface;
}
