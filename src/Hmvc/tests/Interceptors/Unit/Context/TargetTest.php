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

        $target = Target::fromReflection($reflection);

        self::assertSame($reflection, $target->getReflection());
        self::assertSame('print_r', (string)$target);
    }

    public function testCreateFromReflectionMethod(): void
    {
        $reflection = new \ReflectionMethod($this, __FUNCTION__);

        $target = Target::fromReflection($reflection);

        self::assertSame($reflection, $target->getReflection());
        self::assertSame(__FUNCTION__, (string)$target);
    }

    public function testWithReflectionFunction(): void
    {
        $reflection = new \ReflectionFunction('print_r');

        $target = Target::fromPathArray(['foo', 'bar']);
        $target2 = $target->withReflection($reflection);

        // Immutability
        self::assertNotSame($target, $target2);
        // First target is not changed
        self::assertSame(['foo', 'bar'], $target->getPath());
        self::assertNull($target->getReflection());
        self::assertSame('foo.bar', (string)$target);
        // Second target is changed
        self::assertSame(['foo', 'bar'], $target2->getPath());
        self::assertSame($reflection, $target2->getReflection());
        // Reflection does'n affect the string representation if path is set
        self::assertSame('foo.bar', (string)$target);
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
        yield 'not a class' => ['Spiral\Tests\Interceptors\Unit\Stub\FooBarBaz', 'noExistingMethod', false];
    }

    #[DataProvider('providePairs')]
    public function testCreateFromPair(string $controller, string $action, bool $hasReflection): void
    {
        $target = Target::fromPair($controller, $action);

        self::assertSame([$controller, $action], $target->getPath());
        $reflection = $target->getReflection();
        self::assertSame($hasReflection, $reflection !== null);
        if ($hasReflection) {
            self::assertInstanceOf(\ReflectionMethod::class, $reflection);
            self::assertSame($action, $reflection->getName());
        }
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
