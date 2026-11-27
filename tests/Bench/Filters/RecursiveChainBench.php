<?php

declare(strict_types=1);

namespace Spiral\Tests\Bench\Filters;

use Spiral\Filters\InputInterface;
use Spiral\Tests\Bench\Filters\Fixture\WideNodeFilter;
use Tally\Attribute\Before;
use Tally\Attribute\DataProvider;
use Tally\Attribute\Linear;

/**
 * A chain of self-referencing filters with ten fields each, one child per level.
 */
final class RecursiveChainBench
{
    private FilterStand $stand;
    private InputInterface $input;

    /**
     * @return iterable<string, array{int, int}>
     */
    public static function shortKeys(): iterable
    {
        yield 'depth-10' => [10, 1];
        yield 'depth-20' => [20, 1];
    }

    /**
     * @return iterable<string, array{int, int}>
     */
    public static function longKeys(): iterable
    {
        yield 'depth-10' => [10, 1024];
        yield 'depth-20' => [20, 1024];
    }

    #[DataProvider('shortKeys')]
    #[Linear('depth')]
    public function benchShortKeys(int $depth, int $keyLength): void
    {
        $this->stand->create(WideNodeFilter::class, $this->input);
    }

    #[DataProvider('longKeys')]
    #[Linear('depth')]
    public function benchLongKeys(int $depth, int $keyLength): void
    {
        $this->stand->create(WideNodeFilter::class, $this->input);
    }

    #[Before]
    private function prepare(int $depth, int $keyLength): void
    {
        $this->stand = new FilterStand();
        $this->stand->warmUp(WideNodeFilter::class, ['children' => [['children' => []]]]);

        $key = \str_repeat('k', $keyLength);
        $node = ['children' => []];
        for ($i = 0; $i < $depth; ++$i) {
            $node = ['children' => [$key => $node]];
        }

        $this->input = $this->stand->input($node);
    }
}
