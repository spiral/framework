<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Validation\Checkers;

use Spiral\Tests\Validation\BaseTest;
use Spiral\Validation\Checker\ArrayChecker;

class ArrayTest extends BaseTest
{
    public function testOf(): void
    {
        /** @var ArrayChecker $checker */
        $checker = $this->container->get(ArrayChecker::class);

        $this->assertTrue($checker->of([1], 'is_int'));
        $this->assertTrue($checker->of([1], 'integer'));
        $this->assertTrue($checker->of(['1'], 'is_string'));

        $this->assertFalse($checker->of(1, 'is_int'));
        $this->assertFalse($checker->of([1], 'is_string'));
    }

    public function testCount(): void
    {
        /** @var ArrayChecker $checker */
        $checker = $this->container->get(ArrayChecker::class);

        $this->assertFalse($checker->count('foobar', 1));
        $this->assertTrue($checker->count($this->createCountable(2), 2));
        $this->assertTrue($checker->count([1, 2], 2));
        $this->assertFalse($checker->count([1, 2], 3));
    }

    public function testLonger(): void
    {
        /** @var ArrayChecker $checker */
        $checker = $this->container->get(ArrayChecker::class);

        $this->assertFalse($checker->longer('foobar', 1));
        $this->assertTrue($checker->longer($this->createCountable(2), 1));
        $this->assertTrue($checker->longer([1, 2], 1));
        $this->assertTrue($checker->longer([1, 2], 2));
        $this->assertFalse($checker->longer([1, 2], 3));
    }

    public function testShorter(): void
    {
        /** @var ArrayChecker $checker */
        $checker = $this->container->get(ArrayChecker::class);

        $this->assertFalse($checker->shorter('foobar', 1));
        $this->assertTrue($checker->shorter($this->createCountable(2), 3));
        $this->assertTrue($checker->shorter([1, 2], 3));
        $this->assertTrue($checker->shorter([1, 2], 2));
        $this->assertFalse($checker->shorter([1, 2], 1));
    }

    public function testRange(): void
    {
        /** @var ArrayChecker $checker */
        $checker = $this->container->get(ArrayChecker::class);

        $this->assertFalse($checker->range('foobar', 1, 2));
        $this->assertTrue($checker->range($this->createCountable(2), 0, 2));
        $this->assertTrue($checker->range([1, 2], 1, 2));
        $this->assertTrue($checker->range([1, 2], 2, 3));
        $this->assertFalse($checker->range([1, 2], 0, 0));
        $this->assertFalse($checker->range([1, 2], 3, 4));
    }

    private function createCountable(int $count): \Countable
    {
        return new class($count) implements \Countable {
            private $count;

            public function __construct(int $count)
            {
                $this->count = $count;
            }

            public function count(): int
            {
                return $this->count;
            }
        };
    }
}
