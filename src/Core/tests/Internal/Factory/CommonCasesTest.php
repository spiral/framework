<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Internal\Factory;

use Spiral\Core\Container;
use Spiral\Core\Exception\Container\ContainerException;
use Spiral\Core\FactoryInterface;
use Spiral\Tests\Core\Fixtures\BadClass;
use Spiral\Tests\Core\Fixtures\Bucket;
use Spiral\Tests\Core\Fixtures\CorruptedClass;
use Spiral\Tests\Core\Fixtures\SampleClass;
use Spiral\Tests\Core\Stub\EnumService;
use Spiral\Tests\Core\Stub\LightEngineDecorator;

final class CommonCasesTest extends BaseTest
{
    public function testNotInstantiableEnum(): void
    {
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('Enum `Spiral\Tests\Core\Stub\EnumObject` can not be constructed.');

        $this->make(EnumService::class);
    }

    public function testNotInstantiableAbstract(): void
    {
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('Abstract class `Spiral\Tests\Core\Stub\LightEngine` can not be constructed.');

        $this->make(LightEngineDecorator::class);
    }

    public function testConstructCorrupted(): void
    {
        $this->expectException(\ParseError::class);

        $this->make(CorruptedClass::class);
    }

    public function testAutoFactory(): void
    {
        $bucket = $this->make(Bucket::class, [
            'name' => 'abc',
            'data' => 'some data',
        ]);

        $this->assertInstanceOf(Bucket::class, $bucket);
        $this->assertSame('abc', $bucket->getName());
        $this->assertSame('some data', $bucket->getData());
    }

    public function testClosureFactory(): void
    {
        $this->bind(Bucket::class, function ($data) {
            return new Bucket('via-closure', $data);
        });

        $bucket = $this->make(Bucket::class, [
            'data' => 'some data',
        ]);

        $this->assertInstanceOf(Bucket::class, $bucket);
        $this->assertSame('via-closure', $bucket->getName());
        $this->assertSame('some data', $bucket->getData());
    }

    public function testPrivateMethodFactory(): void
    {
        $this->bind(Bucket::class, [self::class, 'makeBucket']);

        $bucket = $this->make(Bucket::class, [
            'data' => 'some data',
        ]);

        $this->assertInstanceOf(Bucket::class, $bucket);
        $this->assertSame('via-method', $bucket->getName());
        $this->assertSame('some data', $bucket->getData());
    }

    public function testCascadeFactory(): void
    {
        $sample = new SampleClass();

        $this->bind(Bucket::class, [self::class, 'makeBucketWithSample']);
        $this->bind(SampleClass::class, function () use ($sample) {
            return $sample;
        });

        $bucket = $this->make(Bucket::class);

        $this->assertInstanceOf(Bucket::class, $bucket);
        $this->assertSame('via-method-with-sample', $bucket->getName());
        $this->assertSame($sample, $bucket->getData());
    }

    /**
     * @param mixed $data
     *
     * @return Bucket
     */
    private function makeBucket($data)
    {
        return new Bucket('via-method', $data);
    }

    /**
     * @param SampleClass $sample
     *
     * @return Bucket
     */
    private function makeBucketWithSample(SampleClass $sample)
    {
        return new Bucket('via-method-with-sample', $sample);
    }
}
