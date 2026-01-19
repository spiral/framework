<?php

declare(strict_types=1);

namespace Spiral\Tests\Core;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;
use Spiral\Core\Exception\Container\NotCallableException;
use Spiral\Core\Exception\Container\NotFoundException;
use Spiral\Core\Exception\Resolver\ArgumentResolvingException;
use Spiral\Core\InvokerInterface;
use Spiral\Core\Scope;
use Spiral\Core\ScopeInterface;
use Spiral\Tests\Core\Fixtures\Bucket;
use Spiral\Tests\Core\Fixtures\PrivateConstructor;
use Spiral\Tests\Core\Fixtures\SampleClass;
use Spiral\Tests\Core\Fixtures\Storage;

class InvokerTest extends TestCase
{
    private Container $container;

    public function testCallValidCallableArray(): void
    {
        $this->container->bindSingleton(Bucket::class, $bucket = new Bucket('foo'));
        $object = new Storage();

        $result = $this->container->invoke([$object, 'makeBucket'], ['name' => 'bar']);

        self::assertSame($bucket, $result['bucket']);
        self::assertInstanceOf(SampleClass::class, $result['class']);
        self::assertSame('bar', $result['name']);
        self::assertSame('baz', $result['path']);
    }

    public function testCallValidCallableArrayWithClassResolving(): void
    {
        $this->container->bindSingleton(Bucket::class, $bucket = new Bucket('foo'));

        $result = $this->container->invoke([Storage::class, 'makeBucket'], ['name' => 'bar']);

        self::assertSame($bucket, $result['bucket']);
        self::assertInstanceOf(SampleClass::class, $result['class']);
        self::assertSame('bar', $result['name']);
        self::assertSame('baz', $result['path']);
    }

    public function testCallValidCallableArrayWithResolvingFromContainer(): void
    {
        $this->container->bindSingleton('foo', new Storage());
        $this->container->bindSingleton(Bucket::class, $bucket = new Bucket('foo'));

        $result = $this->container->invoke(['foo', 'makeBucket'], ['name' => 'bar']);

        self::assertSame($bucket, $result['bucket']);
        self::assertInstanceOf(SampleClass::class, $result['class']);
        self::assertSame('bar', $result['name']);
        self::assertSame('baz', $result['path']);
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

        $result = $this->container->invoke(Storage::class . '::createBucket', ['name' => 'bar']);

        self::assertSame($bucket, $result['bucket']);
        self::assertInstanceOf(SampleClass::class, $result['class']);
        self::assertSame('bar', $result['name']);
        self::assertSame('baz', $result['path']);
    }

    /**
     * The Invoker must not instantiate the class when calling a static method.
     * In this case, we don't make any bindings for the class.
     */
    public function testCallStaticMethodWithoutInstantiation(): void
    {
        $result = $this->container->invoke([PrivateConstructor::class, 'publicMethod'], [42]);

        self::assertSame(42, $result);
    }

    /**
     * The Invoker must not instantiate the class when calling a static method.
     * In this case, we make alias bindings for the class.
     */
    public function testCallStaticMethodWithoutInstantiationAliased(): void
    {
        $this->container->bind('foo', PrivateConstructor::class);
        $result = $this->container->invoke(['foo', 'publicMethod'], [42]);

        self::assertSame(42, $result);
    }

    /**
     * The Invoker must not instantiate the class when calling a static method and find the binding in all the scopes
     */
    public function testCallStaticMethodWithoutInstantiationAliasedScoped(): void
    {
        $this->container->bind('alias', PrivateConstructor::class);

        $result = $this->container->runScope(
            new Scope('foo'),
            fn(ScopeInterface $c): mixed => $c->runScope(
                new Scope('bar'),
                fn(InvokerInterface $i): mixed => $i->invoke(['alias', 'publicMethod'], [42]),
            ),
        );

        self::assertSame(42, $result);
    }

    /**
     * The Invoker must not instantiate the class when calling a static method.
     * In this case, we make a typed factory binding for the class.
     */
    public function testCallStaticMethodWithoutInstantiationWithFactory(): void
    {
        $this->container->bind('foo', fn(): PrivateConstructor => throw new \Exception('Should not be called'));
        $result = $this->container->invoke(['foo', 'publicMethod'], [42]);

        self::assertSame(42, $result);
    }

    /**
     * The Invoker must instantiate the dependency if it cannot detect the return type.
     */
    public function testCallStaticMethodWithoutInstantiationWithUntypedFactory(): void
    {
        // Note: do not add a return type to the closure
        $this->container->bind('foo', fn() => throw new \Exception('Factory called'));

        try {
            $this->container->invoke(['foo', 'publicMethod'], [42]);
            self::fail('Exception should be thrown');
        } catch (\Throwable $e) {
            self::assertInstanceOf(NotFoundException::class, $e);
            self::assertNotNull($e->getPrevious());
            self::assertStringContainsString('Factory called', $e->getPrevious()->getMessage());
        }
    }

    /**
     * The Invoker must instantiate the dependency if it cannot detect the return type.
     */
    public function testCallStaticMethodWithoutInstantiationWithOvertypedFactory(): void
    {
        $this->container->bind('foo', fn(): PrivateConstructor|SampleClass => throw new \Exception('Factory called'));

        try {
            $this->container->invoke(['foo', 'publicMethod'], [42]);
            self::fail('Exception should be thrown');
        } catch (\Throwable $e) {
            self::assertInstanceOf(NotFoundException::class, $e);
            self::assertNotNull($e->getPrevious());
            self::assertStringContainsString('Factory called', $e->getPrevious()->getMessage());
        }
    }

    /**
     * The Invoker must instantiate the dependency if it cannot detect the return type.
     */
    public function testCallStaticMethodWithoutInstantiationWithNullableTypedFactory(): void
    {
        $this->container->bind('foo', fn(): ?PrivateConstructor => throw new \Exception('Factory called'));
        try {
            $this->container->invoke(['foo', 'publicMethod'], [42]);
            self::fail('Exception should be thrown');
        } catch (\Throwable $e) {
            self::assertInstanceOf(NotFoundException::class, $e);
            self::assertNotNull($e->getPrevious());
            self::assertStringContainsString('Factory called', $e->getPrevious()->getMessage());
        }
    }

    public function testCallValidCallableStringWithNotResolvableDependencies(): void
    {
        $this->expectException(ArgumentResolvingException::class);
        $this->expectExceptionMessage('Unable to resolve required argument `name` when resolving');

        $this->container->invoke(Storage::class . '::createBucket', ['name' => 'bar']);
    }

    public function testCallValidClosure(): void
    {
        $this->container->bindSingleton(Bucket::class, $bucket = new Bucket('foo'));

        $result = $this->container->invoke(
            static fn(Bucket $bucket, SampleClass $class, string $name, string $path = 'baz'): array => \compact('bucket', 'class', 'name', 'path'),
            ['name' => 'bar'],
        );

        self::assertSame($bucket, $result['bucket']);
        self::assertInstanceOf(SampleClass::class, $result['class']);
        self::assertSame('bar', $result['name']);
        self::assertSame('baz', $result['path']);
    }

    public function testCallValidClosureWithNotResolvableDependencies(): void
    {
        $this->expectException(ArgumentResolvingException::class);
        $this->expectExceptionMessage('Unable to resolve required argument `name` when resolving');

        $this->container->invoke(
            static fn(Bucket $bucket, SampleClass $class, string $name, string $path = 'baz'): array => \compact('bucket', 'class', 'name', 'path'),
            ['name' => 'bar'],
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

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = new Container();
    }
}
