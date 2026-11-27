<?php

declare(strict_types=1);

namespace Spiral\Tests\Bench\Filters;

use Spiral\Filters\InputInterface;
use Spiral\Tests\Filters\Fixtures\TreeFilter;
use Tally\Attribute\Before;
use Tally\Attribute\DataProvider;
use Tally\Attribute\Linear;

/**
 * A tree whose only branch has a key of the longest allowed length, so every leaf below it gets a long
 * input prefix.
 */
final class LongKeyTreeBench
{
    private FilterStand $stand;
    private InputInterface $input;

    /**
     * @return iterable<string, array{int}>
     */
    public static function sizes(): iterable
    {
        yield 'leaves-625' => [625];
        yield 'leaves-1250' => [1250];
        yield 'leaves-2500' => [2500];
    }

    #[DataProvider('sizes')]
    #[Linear('leaves')]
    public function benchLeaves(int $leaves): void
    {
        $this->stand->create(TreeFilter::class, $this->input);
    }

    #[Before]
    private function prepare(int $leaves): void
    {
        $this->stand = new FilterStand();
        $this->stand->warmUp(TreeFilter::class, ['branches' => [['leaves' => [0]]]]);

        // 1024 bytes of two-byte characters.
        $key = \str_repeat('è', 512);
        $this->input = $this->stand->input(['branches' => [$key => ['leaves' => \array_fill(0, $leaves, 0)]]]);
    }
}
