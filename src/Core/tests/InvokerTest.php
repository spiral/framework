<?php

declare(strict_types=1);

namespace Spiral\Tests\Core;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;
use Spiral\Core\Exception\Container\ArgumentException;
use Spiral\Core\Exception\Container\NotCallableException;
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

        $result = $this->container->call([$object, 'makeBucket'], ['name' => 'bar']);

        $this->assertSame($bucket, $result['bucket']);
        $this->assertInstanceOf(SampleClass::class, $result['class']);
        $this->assertSame('bar', $result['name']);
        $this->assertSame('baz', $result['path']);
    }

    public function testCallValidCallableArrayWithClassResolving(): void
    {
        $this->container->bindSingleton(Bucket::class, $bucket = new Bucket('foo'));

        $result = $this->container->call([Storage::class, 'makeBucket'], ['name' => 'bar']);

        $this->assertSame($bucket, $result['bucket']);
        $this->assertInstanceOf(SampleClass::class, $result['class']);
        $this->assertSame('bar', $result['name']);
        $this->assertSame('baz', $result['path']);
    }

    public function testCallValidCallableArrayWithResolvingFromContainer(): void
    {
        $this->container->bindSingleton('foo', new Storage());
        $this->container->bindSingleton(Bucket::class, $bucket = new Bucket('foo'));

        $result = $this->container->call(['foo', 'makeBucket'], ['name' => 'bar']);

        $this->assertSame($bucket, $result['bucket']);
        $this->assertInstanceOf(SampleClass::class, $result['class']);
        $this->assertSame('bar', $result['name']);
        $this->assertSame('baz', $result['path']);
    }

    public function testCallValidCallableArrayWithNotResolvableDependencies(): void
    {
        $this->expectException(ArgumentException::class);
        $this->expectErrorMessage(
            "Unable to resolve 'name' argument in 'Spiral\Tests\Core\Fixtures\Bucket::__construct'"
        );
        $object = new Storage();

        $this->container->call([$object, 'makeBucket'], ['name' => 'bar']);
    }

    public function testCallValidCallableString(): void
    {
        $this->container->bindSingleton(Bucket::class, $bucket = new Bucket('foo'));

        $result = $this->container->call(Storage::class.'::createBucket', ['name' => 'bar']);

        $this->assertSame($bucket, $result['bucket']);
        $this->assertInstanceOf(SampleClass::class, $result['class']);
        $this->assertSame('bar', $result['name']);
        $this->assertSame('baz', $result['path']);
    }

    public function testCallValidCallableStringWithNotResolvableDependencies(): void
    {
        $this->expectException(ArgumentException::class);
        $this->expectErrorMessage(
            "Unable to resolve 'name' argument in 'Spiral\Tests\Core\Fixtures\Bucket::__construct'"
        );
        $this->container->call(Storage::class.'::createBucket', ['name' => 'bar']);
    }

    public function testCallValidClosure(): void
    {
        $this->container->bindSingleton(Bucket::class, $bucket = new Bucket('foo'));

        $result = $this->container->call(
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
        $this->expectException(ArgumentException::class);
        $this->expectErrorMessage(
            "Unable to resolve 'name' argument in 'Spiral\Tests\Core\Fixtures\Bucket::__construct'"
        );

        $this->container->call(
            static function (Bucket $bucket, SampleClass $class, string $name, string $path = 'baz') {
                return \compact('bucket', 'class', 'name', 'path');
            },
            ['name' => 'bar']
        );
    }

    public function testInvalidCallableStringShouldThrowAnException(): void
    {
        $this->expectException(NotCallableException::class);
        $this->expectErrorMessage(
            "'foobar' is not a callable"
        );

        $this->container->call('foobar');
    }

    public function testInvalidCallableArrayShouldThrowAnException(): void
    {
        $this->expectException(NotCallableException::class);
        $this->expectErrorMessage(
            "array (  0 =>   Spiral\Tests\Core\Fixtures\Storage::__set_state(array(  )),) is not a callable"
        );
        $object = new Storage();

        $this->container->call([$object]);
    }
}
