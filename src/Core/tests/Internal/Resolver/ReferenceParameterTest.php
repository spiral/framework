<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Internal\Resolver;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;

class ReferenceParameterTest extends BaseTest
{
    public function testReferencedVariadicParameterAndUnnamedArguments(): void
    {
        $foo = new DateTimeImmutable();
        $bar = new DateTimeImmutable();
        $baz = new DateTimeImmutable();
        $fiz = new DateTime();

        $result = $this->resolveClosure(
            static fn(DateTimeInterface &...$dates) => $dates,
            [$foo, &$bar, &$baz, $fiz]
        );
        $this->assertCount(4, $result);
        $this->assertSame([$foo, $bar, $baz, $fiz], $result);
        $this->assertInstanceOf(DateTimeImmutable::class, $result[0]);
        $this->assertInstanceOf(DateTimeImmutable::class, $result[1]);
        $this->assertInstanceOf(DateTimeImmutable::class, $result[2]);
        $this->assertInstanceOf(DateTime::class, $result[3]);

        $foo = 'foo';
        $bar = 'bar';
        $baz = 'baz';
        $fiz = 'fiz';

        $this->assertInstanceOf(DateTimeImmutable::class, $result[0]);
        $this->assertSame('bar', $result[1]);
        $this->assertSame('baz', $result[2]);
        $this->assertInstanceOf(DateTime::class, $result[3]);
    }

    public function testReferencedVariadicParameterWithNamedArgument(): void
    {
        $foo = new DateTimeImmutable();
        $bar = new DateTimeImmutable();
        $baz = new DateTimeImmutable();
        $fiz = new DateTime();

        $result = $this->resolveClosure(
            static fn(DateTimeInterface &...$dates) => $dates,
            ['dates' => [$foo, &$bar, &$baz, $fiz]]
        );
        $this->assertCount(4, $result);
        $this->assertSame([$foo, $bar, $baz, $fiz], $result);
        $this->assertInstanceOf(DateTimeImmutable::class, $result[0]);
        $this->assertInstanceOf(DateTimeImmutable::class, $result[1]);
        $this->assertInstanceOf(DateTimeImmutable::class, $result[2]);
        $this->assertInstanceOf(DateTime::class, $result[3]);

        $foo = 'foo';
        $bar = 'bar';
        $baz = 'baz';
        $fiz = 'fiz';

        $this->assertInstanceOf(DateTimeImmutable::class, $result[0]);
        $this->assertSame('bar', $result[1]);
        $this->assertSame('baz', $result[2]);
        $this->assertInstanceOf(DateTime::class, $result[3]);
    }
}
