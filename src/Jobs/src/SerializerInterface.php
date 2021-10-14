<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Jobs;

/**
 * Serializes job payloads.
 */
interface SerializerInterface
{
    /**
     * Serialize payload.
     *
     * @param string $jobType
     * @param array  $payload
     * @return string
     */
    public function serialize(string $jobType, array $payload): string;
}
