<?php

declare(strict_types=1);

namespace Spiral\Distribution\Internal;

/**
 * @internal DateTimeFactoryInterface is an internal library interface, please do not use it in your code.
 * @psalm-internal Spiral\Distribution
 *
 * Please note that this interface currently emulates the behavior of the
 * PSR-20 implementation and may be replaced by the `psr/clock` implementation
 * in future versions.
 */
interface DateTimeFactoryInterface
{
    /**
     * Returns the current time as a DateTimeImmutable instance.
     */
    public function now(): \DateTimeImmutable;
}
