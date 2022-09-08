<?php

declare(strict_types=1);

namespace Spiral\Events\Processor;

interface ProcessorInterface
{
    public function process(): void;
}
