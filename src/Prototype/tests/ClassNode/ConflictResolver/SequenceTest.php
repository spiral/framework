<?php

declare(strict_types=1);

namespace Spiral\Tests\Prototype\ClassNode\ConflictResolver;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Spiral\Core\Container;
use Spiral\Prototype\ClassNode\ConflictResolver\Sequences;

class SequenceTest extends TestCase
{
    #[DataProvider('findProvider')]
    public function testFind(array $sequence, int $pos, int $expected): void
    {
        $this->assertEquals($expected, $this->sequences()->find($sequence, $pos));
    }

    public static function findProvider(): \Traversable
    {
        // empty input
        yield [[], 5, 5];
        // in the gap (not taken)
        yield [[3, 4, 8, 9,], 6, 6];
        yield [[3, 4, 8, 9,], 1, 1];
        // in the sequence (taken)
        yield [[3, 4, 8, 9,], 4, 0];
        yield [[0, 1, 4, 5,], 5, 2];
        // do not use "1"
        yield [[0, 3, 4, 8,], 4, 2];
        // full sequence, take next
        yield [[0, 1, 2, 3,], 3, 4];
        yield [[0], 0, 2];
    }

    private function sequences(): Sequences
    {
        $container = new Container();

        return $container->get(Sequences::class);
    }
}
