<?php

declare(strict_types=1);

namespace Spiral\SendIt;

use Spiral\Jobs\SerializerInterface;

final class JsonJobSerializer implements SerializerInterface
{
    public function serialize(string $jobType, array $payload): string
    {
        return json_encode($payload);
    }
}
