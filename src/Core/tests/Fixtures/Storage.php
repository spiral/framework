<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Fixtures;

final class Storage
{
    private function makeBucket(Bucket $bucket, SampleClass $class, string $name, string $path = 'baz'): array
    {
        return \compact('bucket', 'class', 'name', 'path');
    }

    public static function createBucket(Bucket $bucket, SampleClass $class, string $name, string $path = 'baz'): array
    {
        return \compact('bucket', 'class', 'name', 'path');
    }
}
