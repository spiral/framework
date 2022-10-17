<?php

declare(strict_types=1);

namespace Spiral\Telemetry;

interface TracerFactoryInterface
{
    /**
     * Make tracer object with given context
     */
    public function fromContext(?array $context): TracerInterface;
}
