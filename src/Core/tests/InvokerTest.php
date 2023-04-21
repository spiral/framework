<?php

declare(strict_types=1);

namespace Spiral\Tests\Core;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;
use Spiral\Core\Exception\Container\NotCallableException;
use Spiral\Core\Exception\Resolver\ArgumentResolvingException;
use Spiral\Tests\Core\Fixtures\Bucket;
use Spiral\Tests\Core\Fixtures\SampleClass;
use Spiral\Tests\Core\Fixtures\Storage;

class InvokerTest extends TestCase
{
    /** @var Container */
    private $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = new Container();
    }

    public function testCallValidCallableArray(): void
    {
        $this->container->bindSingleton(Bucket::class, $bucket = new Bucket('foo'));
        $object = new Storage();

        $result = $this->container->invoke([$object, 'makeBucket'], ['name' => 'bar']);

        $this->assertSame($bucket, $result['bucket']);
        $this->assertInstanceOf(SampleClass::class, $result['class']);
        $this->assertSame('bar', $result['name']);
        $this->assertSame('baz', $result['path']);
    }

    public function testCallValidCallableArrayWithClassResolving(): void
    {
        $this->container->bindSingleton(Bucket::class, $bucket = new Bucket('foo'));

        $result = $this->container->invoke([Storage::class, 'makeBucket'], ['name' => 'bar']);

        $this->assertSame($bucket, $result['bucket']);
        $this->assertInstanceOf(SampleClass::class, $result['class']);
        $this->assertSame('bar', $result['name']);
        $this->assertSame('baz', $result['path']);
    }

    public function testCallValidCallableArrayWithResolvingFromContainer(): void
    {
        $this->container->bindSingleton('foo', new Storage());
        $this->container->bindSingleton(Bucket::class, $bucket = new Bucket('foo'));

        $result = $this->container->invoke(['foo', 'makeBucket'], ['name' => 'bar']);

        $this->assertSame($bucket, $result['bucket']);
        $this->assertInstanceOf(SampleClass::class, $result['class']);
        $this->assertSame('bar', $result['name']);
        $this->assertSame('baz', $result['path']);
    }

    public function testCallValidCallableArrayWithNotResolvableDependencies(): void
    {
        $this->expectException(ArgumentResolvingException::class);
        $this->expectExceptionMessage('Unable to resolve required argument `name` when resolving');

        $this->container->invoke([new Storage(), 'makeBucket'], ['name' => 'bar']);
    }

    public function testCallValidCallableString(): void
    {
        $this->container->bindSingleton(Bucket::class, $bucket = new Bucket('foo'));

        $result = $this->container->invoke(Storage::class.'::createBucket', ['name' => 'bar']);

        $this->assertSame($bucket, $result['bucket']);
        $this->assertInstanceOf(SampleClass::class, $result['class']);
        $this->assertSame('bar', $result['name']);
        $this->assertSame('baz', $result['path']);
    }

    public function testCallValidCallableStringWithNotResolvableDependencies(): void
    {
        $this->expectException(ArgumentResolvingException::class);
        $this->expectExceptionMessage('Unable to resolve required argument `name` when resolving');

        $this->container->invoke(Storage::class.'::createBucket', ['name' => 'bar']);
    }

    public function testCallValidClosure(): void
    {
        $this->container->bindSingleton(Bucket::class, $bucket = new Bucket('foo'));

        $result = $this->container->invoke(
            static function (Bucket $bucket, SampleClass $class, string $name, string $path = 'baz') {
                return \compact('bucket', 'class', 'name', 'path');
            },
            ['name' => 'bar']
        );

        $this->assertSame($bucket, $result['bucket']);
        $this->assertInstanceOf(SampleClass::class, $result['class']);
        $this->assertSame('bar', $result['name']);
        $this->assertSame('baz', $result['path']);
    }

    public function testCallValidClosureWithNotResolvableDependencies(): void
    {
        $this->expectException(ArgumentResolvingException::class);
        $this->expectExceptionMessage('Unable to resolve required argument `name` when resolving');

        $this->container->invoke(
            static function (Bucket $bucket, SampleClass $class, string $name, string $path = 'baz') {
                return \compact('bucket', 'class', 'name', 'path');
            },
            ['name' => 'bar']
        );
    }

    public function testInvalidCallableStringShouldThrowAnException(): void
    {
        $this->expectException(NotCallableException::class);
        $this->expectExceptionMessage('Unsupported callable');

        $this->container->invoke('foobar');
    }

    public function testInvalidCallableArrayShouldThrowAnException(): void
    {
        $this->expectException(NotCallableException::class);
        $this->expectExceptionMessage('Unsupported callable');

        $object = new Storage();

        $this->container->invoke([$object]);
    }
}
