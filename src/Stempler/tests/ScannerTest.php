<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

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

        $this->assertSame(null, $src->peak());
    }

    public function testOffsetEOF(): void
    {
        $src = new StringStream('abc');

        $this->assertSame(false, $src->isEOI());

        $this->assertSame('a', $src->peak());
        $this->assertSame('b', $src->peak());
        $this->assertSame('c', $src->peak());

        $this->assertSame(3, $src->getOffset());
        $this->assertSame(true, $src->isEOI());
    }
}
