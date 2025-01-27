<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Internal\Resolver;

use DateTimeInterface;
use Spiral\Tests\Core\Stub\ColorInterface;
use Spiral\Tests\Core\Stub\EngineInterface;
use Spiral\Tests\Core\Stub\EngineMarkTwo;

final class ReferenceParameterTest extends BaseTestCase
{
    public function testReferencedVariadicParameterAndUnnamedArguments(): void
    {
        $foo = new \DateTimeImmutable();
        $bar = new \DateTimeImmutable();
        $baz = new \DateTimeImmutable();
        $fiz = new \DateTime();

        $result = $this->resolveClosure(
            static fn(DateTimeInterface &...$dates) => $dates,
            [[$foo, &$bar, &$baz, $fiz]],
        );
        $this->assertCount(4, $result);
        $this->assertSame([$foo, $bar, $baz, $fiz], $result);
        $this->assertInstanceOf(\DateTimeImmutable::class, $result[0]);
        $this->assertInstanceOf(\DateTimeImmutable::class, $result[1]);
        $this->assertInstanceOf(\DateTimeImmutable::class, $result[2]);
        $this->assertInstanceOf(\DateTime::class, $result[3]);

        $foo = 'foo';
        $bar = 'bar';
        $baz = 'baz';
        $fiz = 'fiz';

        $this->assertInstanceOf(\DateTimeImmutable::class, $result[0]);
        $this->assertSame('bar', $result[1]);
        $this->assertSame('baz', $result[2]);
        $this->assertInstanceOf(\DateTime::class, $result[3]);
    }

    public function testReferencedVariadicParameterWithNamedArgument(): void
    {
        $foo = new \DateTimeImmutable();
        $bar = new \DateTimeImmutable();
        $baz = new \DateTimeImmutable();
        $fiz = new \DateTime();

        $result = $this->resolveClosure(
            static fn(DateTimeInterface &...$dates) => $dates,
            ['dates' => [$foo, &$bar, &$baz, $fiz]],
        );
        $this->assertCount(4, $result);
        $this->assertSame([$foo, $bar, $baz, $fiz], $result);
        $this->assertInstanceOf(\DateTimeImmutable::class, $result[0]);
        $this->assertInstanceOf(\DateTimeImmutable::class, $result[1]);
        $this->assertInstanceOf(\DateTimeImmutable::class, $result[2]);
        $this->assertInstanceOf(\DateTime::class, $result[3]);

        $foo = 'foo';
        $bar = 'bar';
        $baz = 'baz';
        $fiz = 'fiz';

        $this->assertInstanceOf(\DateTimeImmutable::class, $result[0]);
        $this->assertSame('bar', $result[1]);
        $this->assertSame('baz', $result[2]);
        $this->assertInstanceOf(\DateTime::class, $result[3]);
    }

    /**
     * Arguments passed by reference
     */
    public function testInvokeReferencedArguments(): void
    {
        $this->bindSingleton(EngineInterface::class, $engine = new EngineMarkTwo());
        $foo = 1;
        $bar = new \stdClass();
        $baz = null;
        $date1 = new \DateTimeImmutable();
        $date2 = new \DateTime();
        $date3 = new \DateTime();
        $result = $this->resolveClosure(
            static fn(
                int &$foo,
                object &$bar,
                &$baz,
                ?ColorInterface &$nullable,
                EngineInterface &$object, // from container
                DateTimeInterface &...$dates, // collect all unnamed DateTimeInterface objects
            ) => null,
            [
                'dates' => [
                    $date1,
                    $date2,
                    &$date3,
                ],
                'foo' => &$foo,
                'bar' => $bar,
                'baz' => &$baz,
            ],
        );

        $this->assertCount(8, $result);

        $this->assertSame(1, $result[0]);
        $this->assertInstanceOf(\stdClass::class, $result[1]);
        $this->assertNull($result[2]);
        $this->assertNull($result[3]);
        $this->assertSame($engine, $result[4]);
        $this->assertSame($date1, $result[5]);
        $this->assertSame($date2, $result[6]);
        $this->assertSame($date3, $result[7]);

        $result[0] = 0;
        $result[1] = 1;
        $result[2] = 2;
        $result[3] = 3; // no side effect
        $result[4] = 4; // no side effect
        $result[5] = 5; // no side effect
        $result[6] = 6; // no side effect
        $result[7] = 7;
        $this->assertSame(0, $foo);
        $this->assertInstanceOf(\stdClass::class, $bar);
        $this->assertSame(2, $baz);
        $this->assertInstanceOf(EngineMarkTwo::class, $engine);
        $this->assertInstanceOf(\DateTimeInterface::class, $date1);
        $this->assertInstanceOf(\DateTimeInterface::class, $date2);
        $this->assertSame(7, $date3);
    }

    /**
     * Argument that passed by reference will be reference in a resolving result.
     */
    public function testInvokeReferencedArgument(): void
    {
        $this->bindSingleton(EngineInterface::class, new EngineMarkTwo());
        $foo = 1;

        $result = $this->resolveClosure(
            static fn(int $foo) => null,
            ['foo' => &$foo],
        );

        $this->assertSame([$foo], $result);
        $result[0] = 42;
        // $foo has been not changed
        $this->assertSame($result[0], $foo);
    }
}
