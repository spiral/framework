<?php

declare(strict_types=1);

namespace Spiral\Telemetry;

use Spiral\Telemetry\Exception\TracerException;

interface TracerFactoryProviderInterface
{
    /**
     * Get a tracer instance by name.
     *
     * @throws TracerException
     */
    public function getTracerFactory(?string $name = null): TracerFactoryInterface;
}
