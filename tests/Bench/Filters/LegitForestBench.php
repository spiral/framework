<?php

declare(strict_types=1);

namespace Spiral\Tests\Bench\Filters;

use Spiral\Filters\InputInterface;
use Spiral\Tests\Bench\Filters\Fixture\ForestFilter;
use Tally\Attribute\Before;
use Tally\Attribute\DataProvider;
use Tally\Attribute\Linear;

/**
 * A legitimate bulk input three levels deep: trees of branches of leaves.
 */
final class LegitForestBench
{
    private FilterStand $stand;
    private InputInterface $input;

    /**
     * @return iterable<string, array{int, int, int}>
     */
    public static function shapes(): iterable
    {
        yield '10x5x30' => [10, 5, 30];
        yield '10x10x30' => [10, 10, 30];
    }

    #[DataProvider('shapes')]
    #[Linear('branches')]
    public function benchForest(int $trees, int $branches, int $leaves): void
    {
        $this->stand->create(ForestFilter::class, $this->input);
    }

    #[Before]
    private function prepare(int $trees, int $branches, int $leaves): void
    {
        $this->stand = new FilterStand();
        $this->stand->warmUp(ForestFilter::class, ['trees' => [['branches' => [['leaves' => [['name' => 'n']]]]]]]);

        $tree = ['branches' => \array_fill(0, $branches, ['leaves' => \array_fill(0, $leaves, ['name' => 'n'])])];
        $this->input = $this->stand->input(['trees' => \array_fill(0, $trees, $tree)]);
    }
}
