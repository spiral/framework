<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Core;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;
use Spiral\Core\Exception\Container\ContainerException;
use Spiral\Core\FactoryInterface;
use Spiral\Tests\Core\Fixtures\BadClass;
use Spiral\Tests\Core\Fixtures\Bucket;
use Spiral\Tests\Core\Fixtures\CorruptedClass;
use Spiral\Tests\Core\Fixtures\SampleClass;

class FactoryTest extends TestCase
{
    public function testAutoFactory(): void
    {
        $container = new Container();
        $this->assertInstanceOf(FactoryInterface::class, $container);

        $bucket = $container->make(Bucket::class, [
            'name' => 'abc',
            'data' => 'some data',
        ]);

        $this->assertInstanceOf(Bucket::class, $bucket);
        $this->assertSame('abc', $bucket->getName());
        $this->assertSame('some data', $bucket->getData());
    }

    public function testClosureFactory(): void
    {
        $container = new Container();
        $this->assertInstanceOf(FactoryInterface::class, $container);

        $container->bind(Bucket::class, function ($data) {
            return new Bucket('via-closure', $data);
        });

        $bucket = $container->make(Bucket::class, [
            'data' => 'some data',
        ]);

        $this->assertInstanceOf(Bucket::class, $bucket);
        $this->assertSame('via-closure', $bucket->getName());
        $this->assertSame('some data', $bucket->getData());
    }

    public function testMethodFactory(): void
    {
        $container = new Container();
        $this->assertInstanceOf(FactoryInterface::class, $container);

        $container->bind(Bucket::class, [self::class, 'makeBucket']);

        $bucket = $container->make(Bucket::class, [
            'data' => 'some data',
        ]);

        $this->assertInstanceOf(Bucket::class, $bucket);
        $this->assertSame('via-method', $bucket->getName());
        $this->assertSame('some data', $bucket->getData());
    }

    public function testCascadeFactory(): void
    {
        $container = new Container();
        $this->assertInstanceOf(FactoryInterface::class, $container);

        $sample = new SampleClass();

        $container->bind(Bucket::class, [self::class, 'makeBucketWithSample']);
        $container->bind(SampleClass::class, function () use ($sample) {
            return $sample;
        });

        $bucket = $container->make(Bucket::class);

        $this->assertInstanceOf(Bucket::class, $bucket);
        $this->assertSame('via-method-with-sample', $bucket->getName());
        $this->assertSame($sample, $bucket->getData());
    }

    public function testConstructAbstract(): void
    {
        $this->expectException(ContainerException::class);
        $container = new Container();
        $container->make(BadClass::class);
    }

    public function testConstructCorrupted(): void
    {
        $this->expectException(\ParseError::class);
        $container = new Container();
        $container->make(CorruptedClass::class);
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
