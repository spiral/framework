<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Auth;

use DateTimeInterface;

/**
 * Carries information about current authentication token, it's expiration time and actor provider specific payload.
 */
interface TokenInterface
{
    public function getID(): string;

    public function getExpiresAt(): ?DateTimeInterface;

    /**
     * Actor provider specific payload.
     */
    public function getPayload(): array;
}
