<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Internal\Factory;

use Spiral\Core\BinderInterface;
use Spiral\Core\Container\Autowire;
use Spiral\Core\Exception\Container\ContainerException;
use Spiral\Core\Exception\Container\NotFoundException;
use Spiral\Tests\Core\Fixtures\Bucket;
use Spiral\Tests\Core\Fixtures\CorruptedClass;
use Spiral\Tests\Core\Fixtures\DatetimeInjector;
use Spiral\Tests\Core\Fixtures\Factory;
use Spiral\Tests\Core\Fixtures\SampleClass;
use Spiral\Tests\Core\Stub\EnumService;
use Spiral\Tests\Core\Stub\LightEngineDecorator;

final class CommonCasesTest extends BaseTestCase
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

    public function testNotExistingClass(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage(
            'Can\'t resolve `\Foo\Bar\Class\Not\Exists`: undefined class or binding `\Foo\Bar\Class\Not\Exists`.',
        );

        $this->make('\\Foo\\Bar\\Class\\Not\\Exists');
    }

    public function testMakeInternalClass(): void
    {
        $object = $this->make(\SplFileObject::class, [
            'filename' => __FILE__,
            // second parameter skipped
            // third parameter skipped
            'context' => null,
            'other-parameter' => true,
        ]);

        $this->assertSame(\basename(__FILE__), $object->getFilename());
    }

    public function testMakeInternalClassWithOptional(): void
    {
        $this->markTestSkipped(
            'Incorrect behavior in a test environment: second parameter defined as non-optional.',
        );

        $object = $this->make(\DateTimeImmutable::class);

        $this->assertInstanceOf(\DateTimeImmutable::class, $object);
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
        $this->bind(Bucket::class, static function ($data) {
            return new Bucket('via-closure', $data);
        });

        $bucket = $this->make(Bucket::class, [
            'data' => 'some data',
        ]);

        $this->assertInstanceOf(Bucket::class, $bucket);
        $this->assertSame('via-closure', $bucket->getName());
        $this->assertSame('some data', $bucket->getData());
    }

    public function testMakeInterfaceWithDefinition(): void
    {
        $this->bindInjector(\DateTimeInterface::class, DatetimeInjector::class);

        $object = $this->make(\DateTimeInterface::class);

        $this->assertInstanceOf(\DateTimeInterface::class, $object);
    }

    public function testPrivateMethodFactory(): void
    {
        $this->bind(Bucket::class, [Factory::class, 'makeBucket']);

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

        $this->bind(Bucket::class, [Factory::class, 'makeBucketWithSample']);
        $this->bind(SampleClass::class, static function () use ($sample) {
            return $sample;
        });

        $bucket = $this->make(Bucket::class);

        $this->assertInstanceOf(Bucket::class, $bucket);
        $this->assertSame('via-method-with-sample', $bucket->getName());
        $this->assertSame($sample, $bucket->getData());
    }

    public function testRemoveBindingAndMake(): void
    {
        $this->bindSingleton('foo', SampleClass::class);
        $old = $this->make('foo');

        $this->constructor->get('binder', BinderInterface::class)->removeBinding('foo');

        $this->bindSingleton('foo', new Autowire(Bucket::class, ['name' => 'foo']));
        $new = $this->make('foo');

        $this->assertInstanceOf(SampleClass::class, $old);
        $this->assertInstanceOf(Bucket::class, $new);
    }
}
