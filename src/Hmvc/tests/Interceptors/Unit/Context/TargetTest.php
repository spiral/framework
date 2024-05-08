<?php

declare(strict_types=1);

namespace Spiral\Tests\Interceptors\Unit\Context;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Spiral\Interceptors\Context\Target;
use Spiral\Tests\Interceptors\Unit\Stub\TestService;

class TargetTest extends TestCase
{
    public function testCreateFromReflectionFunction(): void
    {
        $reflection = new \ReflectionFunction('print_r');

        $target = Target::fromReflectionFunction($reflection, ['print_r-path']);

        self::assertSame($reflection, $target->getReflection());
        self::assertSame('print_r-path', (string)$target);
        self::assertNull($target->getObject());
    }

    public function testCreateFromClosure(): void
    {
        $target = Target::fromClosure(\print_r(...), ['print_r-path']);

        self::assertNotNull($target->getReflection());
        self::assertSame('print_r-path', (string)$target);
        self::assertNull($target->getObject());
    }

    public function testCreateFromClosureWithContext(): void
    {
        $target = Target::fromClosure($this->{__FUNCTION__}(...), ['print_r-path']);

        self::assertNotNull($target->getReflection());
        self::assertSame('print_r-path', (string)$target);
        self::assertNull($target->getObject());
    }

    public function testCreateFromReflectionMethodClassName(): void
    {
        $reflection = new \ReflectionMethod($this, __FUNCTION__);

        $target = Target::fromReflectionMethod($reflection, __CLASS__);

        self::assertSame($reflection, $target->getReflection());
        self::assertSame(__CLASS__ . '->' . __FUNCTION__, (string)$target);
        self::assertNull($target->getObject());
    }

    public function testCreateFromReflectionMethodObject(): void
    {
        $reflection = new \ReflectionMethod($this, __FUNCTION__);

        $target = Target::fromReflectionMethod($reflection, $this);

        self::assertSame($reflection, $target->getReflection());
        self::assertSame(__CLASS__ . '->' . __FUNCTION__, (string)$target);
        self::assertNotNull($target->getObject());
    }

    public function testCreateFromPathStringWithPath(): void
    {
        $str = 'foo.bar.baz';
        $target = Target::fromPathString($str);
        $target2 = $target->withPath(['bar', 'baz']);

        // Immutability
        self::assertNotSame($target, $target2);
        self::assertSame(['bar', 'baz'], $target2->getPath());
        self::assertSame('bar.baz', (string)$target2);
        // First target is not changed
        self::assertSame(['foo', 'bar', 'baz'], $target->getPath());
        self::assertSame($str, (string)$target);
    }

    public static function providePathChunks(): iterable
    {
        yield [['Foo', 'Bar', 'baz'], '.'];
        yield [['Foo', 'Bar', 'baz', 'fiz.baz'], '/'];
        yield [['Foo'], ' '];
        yield [['Foo', '', ''], '-'];
    }

    #[DataProvider('providePathChunks')]
    public function testCreateFromPathString(array $chunks, string $separator): void
    {
        $str = \implode($separator, $chunks);
        $target = Target::fromPathString($str, $separator);

        self::assertSame($chunks, $target->getPath());
        self::assertSame($str, (string)$target);
    }

    #[DataProvider('providePathChunks')]
    public function testCreateFromPathArray(array $chunks, string $separator): void
    {
        $str = \implode($separator, $chunks);
        $target = Target::fromPathArray($chunks, $separator);

        self::assertSame($chunks, $target->getPath());
        self::assertSame($str, (string)$target);
    }

    public static function providePairs(): iterable
    {
        yield 'static method' => [TestService::class, 'toUpperCase', true];
        yield 'public method' => [TestService::class, 'increment', true];
        yield 'protected method' => [TestService::class, 'toLowerCase', true];
        yield 'not existing' => [TestService::class, 'noExistingMethod', false];
        yield 'parent method' => [TestService::class, 'parentMethod', true];
        yield 'not a class' => ['Spiral\Tests\Interceptors\Unit\Stub\FooBarBaz', 'noExistingMethod', false];
    }

    #[DataProvider('providePairs')]
    public function testCreateFromPair(string $controller, string $action, bool $hasReflection): void
    {
        $target = Target::fromPair($controller, $action);

        self::assertSame([$controller, $action], $target->getPath());
        $reflection = $target->getReflection();
        self::assertSame($hasReflection, $reflection !== null);
        self::assertNull($target->getObject());
        if ($hasReflection) {
            self::assertInstanceOf(\ReflectionMethod::class, $reflection);
            self::assertSame($action, $reflection->getName());
        }
    }

    public function testCreateFromObject(): void
    {
        $service = new TestService();
        $target = Target::fromPair($service, 'parentMethod');

        self::assertSame([TestService::class, 'parentMethod'], $target->getPath());
        $reflection = $target->getReflection();
        self::assertInstanceOf(\ReflectionMethod::class, $reflection);
        self::assertSame('parentMethod', $reflection->getName());
        self::assertSame($service, $target->getObject());
    }

    public function testCreateFromPathStringDefaultSeparator(): void
    {
        $str = 'foo.bar.baz';
        $target = Target::fromPathString($str);

        self::assertSame(['foo', 'bar', 'baz'], $target->getPath());
        self::assertSame($str, (string)$target);
    }

    public function testPrivateConstructor(): void
    {
        $this->expectException(\Error::class);

        new Target();
    }
}
