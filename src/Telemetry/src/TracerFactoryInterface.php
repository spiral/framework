<?php

declare(strict_types=1);

namespace Spiral\Telemetry;

/**
 * The component is under development.
 *
 * Something may be changed in the future. We will stable it soon.
 * Feedback is welcome {@link https://github.com/spiral/framework/discussions/822}.
 */
interface TracerFactoryInterface
{
    /**
     * Make tracer object with given context
     */
    public function make(array $context = []): TracerInterface;
}
