<?php

declare(strict_types=1);

namespace Spiral\Queue;

interface SerializerRegistryInterface
{
    public function getSerializer(string $jobType): SerializerInterface;
}
