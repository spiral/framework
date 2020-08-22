<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Validation\Checkers;

use PHPUnit\Framework\TestCase;
use Spiral\Validation\Checker\NumberChecker;

class NumberTest extends TestCase
{
    public function testRange(): void
    {
        $checker = new NumberChecker();

        $this->assertTrue($checker->range(10, 1, 100));
        $this->assertTrue($checker->range(10, 10, 100));
        $this->assertTrue($checker->range(10, 1, 10));

        $this->assertTrue($checker->range(10.5, 1, 100));

        $this->assertFalse($checker->range(10, 11, 100));
        $this->assertFalse($checker->range(10, 10.1, 100));
        $this->assertFalse($checker->range(10, 1, 9.99));

        $this->assertFalse($checker->range(null, 10.1, 100));
        $this->assertFalse($checker->range([], 1, 9.99));
    }

    public function testHigher(): void
    {
        $checker = new NumberChecker();

        $this->assertTrue($checker->higher(10, 10));
        $this->assertTrue($checker->higher(10, 9));
        $this->assertTrue($checker->higher(10, 9.99));

        $this->assertFalse($checker->higher(10, 11));

        $this->assertFalse($checker->higher(null, 11));
        $this->assertFalse($checker->higher([], 11));
    }

    public function testLower(): void
    {
        $checker = new NumberChecker();

        $this->assertTrue($checker->lower(10, 11));
        $this->assertTrue($checker->lower(10, 10.01));

        $this->assertFalse($checker->lower(10, 9.99));

        $this->assertFalse($checker->lower(null, 9.99));
        $this->assertFalse($checker->lower([], 9.99));
    }
}
