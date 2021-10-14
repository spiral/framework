<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Jobs;

final class JsonSerializer implements SerializerInterface
{
    /**
     * @inheritDoc
     */
    public function serialize(string $jobType, array $payload): string
    {
        return json_encode($payload);
    }
}
