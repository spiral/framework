<?php

declare(strict_types=1);

namespace Spiral\Tests\Prototype\ClassNode\ConflictResolver;

use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;
use Spiral\Prototype\ClassNode\ConflictResolver\Sequences;

class SequenceTest extends TestCase
{
    /**
     * @dataProvider findProvider
     *
     * @param array $sequence
     * @param int   $pos
     * @param int   $expected
     */
    public function testFind(array $sequence, int $pos, int $expected): void
    {
        $this->assertEquals($expected, $this->sequences()->find($sequence, $pos));
    }

    public function findProvider(): array
    {
        return [
            // empty input
            [[], 5, 5],
            // in the gap (not taken)
            [[3, 4, 8, 9,], 6, 6],
            [[3, 4, 8, 9,], 1, 1],
            // in the sequence (taken)
            [[3, 4, 8, 9,], 4, 0],
            [[0, 1, 4, 5,], 5, 2],
            // do not use "1"
            [[0, 3, 4, 8,], 4, 2],
            // full sequence, take next
            [[0, 1, 2, 3,], 3, 4],
            [[0], 0, 2],
        ];
    }

    private function sequences(): Sequences
    {
        $container = new Container();

        return $container->get(Sequences::class);
    }
}
