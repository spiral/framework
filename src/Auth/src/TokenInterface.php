<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Auth;

/**
 * Carries information about current authentication token, it's expiration time and actor provider specific payload.
 */
interface TokenInterface
{
    /**
     * @return string
     */
    public function getID(): string;

    /**
     * @return \DateTimeInterface|null
     */
    public function getExpiresAt(): ?\DateTimeInterface;

    /**
     * Actor provider specific payload.
     *
     * @return array
     */
    public function getPayload(): array;
}
