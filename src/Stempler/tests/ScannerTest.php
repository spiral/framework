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
        $this->assertSame('a', $src->peak());
        $this->assertSame('b', $src->peak());
        $this->assertSame('c', $src->peak());

        $this->assertNull($src->peak());
    }

    public function testOffsetEOF(): void
    {
        $src = new StringStream('abc');

        $this->assertFalse($src->isEOI());

        $this->assertSame('a', $src->peak());
        $this->assertSame('b', $src->peak());
        $this->assertSame('c', $src->peak());

        $this->assertSame(3, $src->getOffset());
        $this->assertTrue($src->isEOI());
    }
}
