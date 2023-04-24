<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Fixtures;

final class Factory
{
    public function sampleClass(): SampleClass
    {
        return new SampleClass();
    }

    public function makeStdClass(array $params = []): \stdClass
    {
        return (object)$params;
    }

    private function privateSampleClass(): SampleClass
    {
        return new SampleClass();
    }

    private function makeBucket(mixed $data): Bucket
    {
        return new Bucket('via-method', $data);
    }

    private function makeBucketWithSample(SampleClass $sample): Bucket
    {
        return new Bucket('via-method-with-sample', $sample);
    }
}
