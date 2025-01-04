<?php

declare(strict_types=1);

namespace Spiral\Tests\Stempler;

use PHPUnit\Framework\TestCase;
use Spiral\Stempler\Lexer\StringStream;

class ScannerTest extends TestCase
{
    public function testPeakNull(): void
    {
        $src = new StringStream('abc');
        self::assertSame('a', $src->peak());
        self::assertSame('b', $src->peak());
        self::assertSame('c', $src->peak());

        self::assertNull($src->peak());
    }

    public function testOffsetEOF(): void
    {
        $src = new StringStream('abc');

        self::assertFalse($src->isEOI());

        self::assertSame('a', $src->peak());
        self::assertSame('b', $src->peak());
        self::assertSame('c', $src->peak());

        self::assertSame(3, $src->getOffset());
        self::assertTrue($src->isEOI());
    }
}
